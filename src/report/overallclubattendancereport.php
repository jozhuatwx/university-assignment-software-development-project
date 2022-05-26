<?php
include_once("../server.php");
if (!isLoggedIn()) {
    header("Location: ../login.php");
}
if (!isAdmin()) {
    header("Location: ../nopermission.php");
}

if (isset($_GET["selected_month"]) && !empty($_GET["selected_month"])) {
    $month = date("m", strtotime($_GET["selected_month"]));
    $month_name = date("F", strtotime($_GET["selected_month"]));
    $year = date("Y", strtotime($_GET["selected_month"]));
} else {
    $month = date("m");
    $month_name = date("F");
    $year = date("Y");
}

// Retrieve Clubs
$clubs_sql = "SELECT Club_ID, Club_Name FROM clubs";
$clubs = $link->query($clubs_sql);
$clubs_array = array();
while ($club = $clubs->fetch_assoc()) {
    $clubs_array[] = $club;
}
$totalclubs = $clubs->num_rows;

// Prepared average attendance
$prepared_average_attendance = $link->prepare("SELECT AVG(ap) FROM (
                                                SELECT AVG(attendances.Attendance_Presence) AS ap
                                                FROM attendances
                                                INNER JOIN total_attendances ON attendances.TotalAttendance_ID=total_attendances.TotalAttendance_ID
                                                WHERE attendances.Club_ID=? AND MONTH(total_attendances.TotalAttendance_Date)=? AND YEAR(total_attendances.TotalAttendance_Date)=?
                                                GROUP BY attendances.User_ID
                                            ) AS Average");
$prepared_average_attendance->bind_param("sss", $selected_club_id, $month, $year);
$average_attendance_array = array();

// Prepared meetings
$prepared_meetings = $link->prepare("SELECT DISTINCT attendances.TotalAttendance_ID FROM attendances INNER JOIN total_attendances ON total_attendances.TotalAttendance_ID=attendances.TotalAttendance_ID WHERE Club_ID=? AND MONTH(TotalAttendance_Date)=? AND YEAR(TotalAttendance_Date)=?");
$prepared_meetings->bind_param("sss", $selected_club_id, $month, $year);
$totalmeetings_array = array();

// Prepare attendance
$prepare_no_members =   $link->prepare("SELECT SUM(attendances.Attendance_Presence) AS sp FROM attendances
                        INNER JOIN total_attendances ON attendances.TotalAttendance_ID=total_attendances.TotalAttendance_ID
                        WHERE attendances.Club_ID=? AND MONTH(total_attendances.TotalAttendance_Date)=? AND YEAR(total_attendances.TotalAttendance_Date)=?");
$prepare_no_members->bind_param("sss", $selected_club_id, $month, $year);
$average_no_members_array = array();

// Total number of meetings
foreach ($clubs_array as $club) {
    $selected_club_id = $club["Club_ID"];
    
    $prepared_average_attendance->execute();
    $average_attendance = $prepared_average_attendance->get_result()->fetch_assoc();
    $average_attendance_array[$selected_club_id] = $average_attendance["AVG(ap)"];
    
    $prepared_meetings->execute();
    $totalmeetings = $prepared_meetings->get_result();
    $totalmeetings_array[$selected_club_id] = $totalmeetings->num_rows;

    $prepare_no_members->execute();
    $no_members = $prepare_no_members->get_result()->fetch_assoc();
    if ($totalmeetings->num_rows > 0) {
        $average_no_members_array[$selected_club_id] = $no_members["sp"] / $totalmeetings->num_rows;
    } else {
        $average_no_members_array[$selected_club_id] = 0;
    }
}

// Average number of meetings
$averagenoofmeetings = round(array_sum($totalmeetings_array) / sizeof($clubs_array), 2);

// Overall average number of members / meeting
$averagenomembers = round(array_sum($average_no_members_array), 2);

// Overall tendency to attend
$count = 0;
foreach ($average_attendance_array as $average_attendance) {
    if ($average_attendance > 0) {
        $count++;
    }
}
if ($count > 0) {
    $averagetendency = round(array_sum($average_attendance_array) / $count * 100, 2);
} else {
    $averagetendency = 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>APU Co-curriculum</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <?php include_once("../resource/report.php"); ?>

    <div class="header">
        <p>ASIA PACIFIC UNIVERSITY</p>
        <p>OVERALL CLUB ATTENDANCE REPORT & STATISTICS</p>
        <p>MONTH OF <?php echo strtoupper($month_name) . " " . $year ?></p>
    </div>

    <div class="content">
        <div class="section-title">STATISTICS</div>
        <table>
            <tr>
                <th width="60%">AVERAGE NO. OF MEETINGS</th>
                <td><?php echo $averagenoofmeetings ?></td>
            </tr>
            <tr>
                <th>OVERALL AVERAGE NUMBER OF MEMBERS / MEETING</th>
                <td><?php echo $averagenomembers ?></td>
            </tr>
            <tr>
                <th>OVERALL AVERAGE TENDENCY TO ATTEND</th>
                <td><?php echo $averagetendency ?> %</td>
            </tr>
        </table>

        <table class="barchart-wrapper">
                <tr>
                    <th>OVERALL CLUB ATTENDANCE</td>
                    <th>LEGEND</td>
                </tr>
                <tr>
                    <td width="66.67%">
                    <?php $colours = array("e53935", "d81b60", "8e24aa", "5e35b1", "3949ab", "1e88e5", "039be5", "00acc1", "00897b", "43a047", "7cb342", "c0ca33", "fdd835", "ffb300", "fb8c00", "f4511e", "6d4c41", "757575", "546e7a");
                        $even_distribute = 100 / ($totalclubs + ($totalclubs + 1));
                        $spacing = $even_distribute;
                        
                        echo "<style>";
                        echo ".report-bar { width: $even_distribute%; }";
                        $i = 1;
                        foreach ($clubs_array as $club) {
                            $selected_club_id = $club["Club_ID"];
                            $value = $average_attendance_array[$club["Club_ID"]] * 100;
                            
                            echo ".report-bar.bar$i {";
                                echo "background: #$colours[$i];";
                                echo "left: $spacing%;";
                                echo "height: $value%;";
                            echo "}";
        
                            echo ".report-bar.bar$i:before {";
                                echo "content: '" . round($value, 0) . "%';";
                            echo "}";
        
                            echo ".legend-icon.bar$i {";
                                echo "background-color: #$colours[$i];";
                            echo "}";
        
                            $i++;
                            $spacing += ($even_distribute * 2);
                        }
                        echo "</style>"; ?>
                        <div class="report-barchart" style="height: 37.5vh">
                            <?php for ($x=1; $x <= $i; $x++) { ?>
                                <div class="report-bar bar<?php echo $x ?>"></div>
                            <?php } ?>
                            <!-- https://codepen.io/dxdc100xp/pen/WwMQwE?editors=0100 -->
                        </div>
                    </td>
                    <td width="33.33%" style="padding-left: 20px">
                        <?php
                        $x = 1;
                        foreach ($clubs_array as $club) { ?>
                        <div>
                            <div class="legend-icon bar<?php echo $x ?>"></div>
                            <span class="legend-detail"><?php echo $club["Club_Name"] ?></span>
                        </div>
                        <?php $x++; } ?>
                    </td>
                </tr>
            </table>

        <div class="section-title">CLUB ATTENDANCE</div>
        <table>
            <tr>
                <th width="12.5%">CLUB ID</th>
                <th width="47.5%">CLUB NAME</th>
                <th width="20%">NO. OF MEETING</th>
                <th width="20%">AVERAGE TENDENCY TO ATTEND</th>
            </tr>
            <?php foreach ($clubs_array as $club) {
                $selected_club_id = $club["Club_ID"];
                $prepared_meetings->execute();
                $total_meetings = $totalmeetings_array[$selected_club_id];
                $average_attendance = $average_attendance_array[$selected_club_id]; ?>
            <tr>
                <td><?php echo $selected_club_id ?></td>
                <td><?php echo strtoupper($club["Club_Name"]) ?></td>
                <td><?php echo $total_meetings ?></td>
                <td><?php echo round($average_attendance * 100, 2) ?> %</td>
            </tr>
            <?php }
            $prepared_meetings->close();
            $prepare_no_members->close();
            $prepared_average_attendance->close(); ?>
        </table>
    </div>
    <script>window.print()</script>
</body>
</html>