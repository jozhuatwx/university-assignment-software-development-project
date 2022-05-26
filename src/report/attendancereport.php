<?php
include_once("../server.php");
if (!isLoggedIn()) {
    header("Location: ../login.php");
}

if (isset($_GET["club_id"])) {
    include_once("../phpscript/club.php");
} elseif (isset($_GET["event_id"])) {
    include_once("../phpscript/event.php");
}

if ((isset($club_role) && $club_role <= 2) || (isset($event_role) && $event_role <= 3) || isAdmin()) {
    if (isset($_GET["selected_month"]) && !empty($_GET["selected_month"])) {
        $month = date("m", strtotime($_GET["selected_month"]));
        $month_name = date("F", strtotime($_GET["selected_month"]));
        $year = date("Y", strtotime($_GET["selected_month"]));
    } else {
        $month = date("m");
        $month_name = date("F");
        $year = date("Y");
    }
    
    // Retrieve members
    $members_sql =  "SELECT users.User_ID, users.User_FirstName, users.User_LastName
                    FROM users";
    if (isset($club_id)) {
        $members_sql .= " INNER JOIN users_clubs ON users.User_ID=users_clubs.User_ID
                        WHERE (users_clubs.UsersClub_Role BETWEEN 2 AND 3) AND users_clubs.Club_ID='$club_id'
                        AND NOT users_clubs.UsersClub_Approval='Pending'";
    } elseif (isset($event_id)) {
        $members_sql .= " INNER JOIN users_events ON users.User_ID=users_events.User_ID
                        WHERE (users_events.UsersEvent_Role BETWEEN 2 AND 4) AND users_events.Event_ID='$event_id'
                        AND NOT users_events.UsersEvent_Approval='Pending'";
    }
    $members_sql .= " ORDER BY users.User_FirstName ASC";
    $members = $link->query($members_sql);
    $totalmembers = $members->num_rows;

    $members_array = array();
    while ($member = $members->fetch_assoc()) {
        $members_array[] = $member;
    }

    // Total Meetings
    $meetings_sql =     "SELECT attendances.TotalAttendance_ID, TotalAttendance_Date, TotalAttendance_Quantity
                        FROM attendances INNER JOIN total_attendances ON attendances.TotalAttendance_ID=total_attendances.TotalAttendance_ID
                        WHERE";
    if (isset($club_id)) {
        $meetings_sql .=    " Club_ID='$club_id' AND";
    } elseif (isset($event_id)) {
        $meetings_sql .=    " Event_ID='$event_id' AND";
    }
    $meetings_sql .=    " MONTH(TotalAttendance_Date)='$month'
                        AND YEAR(TotalAttendance_Date)='$year' GROUP BY TotalAttendance_ID";
    $meetings = $link->query($meetings_sql);
    $meetings_array = array();
    while ($meeting = $meetings->fetch_assoc()) {
        $meetings_array[] = $meeting;
    }
    $totalmeetings = $meetings->num_rows;

    // Attendance
    $average_no_members_sql =   "SELECT SUM(attendances.Attendance_Presence) AS sp FROM attendances
                                INNER JOIN total_attendances ON attendances.TotalAttendance_ID=total_attendances.TotalAttendance_ID
                                WHERE MONTH(total_attendances.TotalAttendance_Date)=$month AND YEAR(total_attendances.TotalAttendance_Date)=$year";
    if (isset($club_id)) {
        $average_no_members_sql .= " AND attendances.Club_ID='$club_id'";
    } elseif (isset($event_id)) {
        $average_no_members_sql .= " AND attendances.Event_ID='$event_id'";
    }
    $average_no_members = $link->query($average_no_members_sql)->fetch_assoc();
    if ($totalmeetings > 0) {
        $average_no_member = round($average_no_members["sp"] / $totalmeetings, 2);
    } else {
        $average_no_member = 0;
    }

    $average_tendencies_sql =  "SELECT AVG(ap) FROM (
                                    SELECT AVG(attendances.Attendance_Presence) AS ap
                                    FROM attendances
                                    INNER JOIN total_attendances ON attendances.TotalAttendance_ID=total_attendances.TotalAttendance_ID
                                    WHERE MONTH(total_attendances.TotalAttendance_Date)=$month AND YEAR(total_attendances.TotalAttendance_Date)=$year";
    if (isset($club_id)) {
        $average_tendencies_sql .= " AND attendances.Club_ID='$club_id'";
    } elseif (isset($event_id)) {
        $average_tendencies_sql .= " AND attendances.Event_ID='$event_id'";
    }
    $average_tendencies_sql .=    " GROUP BY attendances.User_ID ) AS Average";
    $average_tendencies = $link->query($average_tendencies_sql)->fetch_assoc();
    $average_tendency = round($average_tendencies["AVG(ap)"] * 100, 2);


    // Prepared Statement
    $average_meeting_attendances_sql =  "SELECT AVG(ap) FROM (
                                            SELECT AVG(attendances.Attendance_Presence) AS ap
                                            FROM attendances
                                            INNER JOIN total_attendances ON attendances.TotalAttendance_ID=total_attendances.TotalAttendance_ID
                                            WHERE TotalAttendance_Date=?";
    if (isset($club_id)) {
        $average_meeting_attendances_sql .= " AND attendances.Club_ID='$club_id'";
    } elseif (isset($event_id)) {
        $average_meeting_attendances_sql .= " AND attendances.Event_ID='$event_id'";
    }
    $average_meeting_attendances_sql .=    " GROUP BY attendances.User_ID ) AS Average";
    $average_meeting_attendances = $link->prepare($average_meeting_attendances_sql);
    $average_meeting_attendances->bind_param("s", $meeting_date);

    $attendance_sql = "SELECT Attendance_Presence FROM attendances INNER JOIN total_attendances ON attendances.TotalAttendance_ID=total_attendances.TotalAttendance_ID WHERE User_ID=? AND TotalAttendance_Date=?";
    if (isset($club_id)) {
        $attendance_sql .= " AND Club_ID='$club_id'";
    } elseif (isset($event_id)) {
        $attendance_sql .= " AND Event_ID='$event_id'";
    }
    $prepared_attendance = $link->prepare($attendance_sql);
    $prepared_attendance->bind_param("ss", $member_user_id, $meeting_date);
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
            <p>ATTENDANCE REPORT & STATISTICS</p>
            <p><?php if (isset($club_name)) {echo strtoupper($club_name);} elseif (isset($event_name)) {echo strtoupper($event_name);} ?></p>
            <p>MONTH OF <?php echo strtoupper($month_name) . " " . $year ?></p>
        </div>
    
        <div class="content">
            <div class="section-title">STATISTICS</div>
            <table>
                <tr>
                    <th width="50%">NUMBER OF MEETINGS</th>
                    <td width="50%"><?php echo $totalmeetings; ?></td>
                </tr>
                <tr>
                    <th>AVERAGE NUMBER OF MEMBERS / MEETING</th>
                    <td><?php echo $average_no_member; ?></td>
                </tr>
                <tr>
                    <th>AVERAGE TENDENCY TO ATTEND</th>
                    <td><?php echo $average_tendency; ?> %</td>
                </tr>
            </table>

            <table class="barchart-wrapper">
                <tr>
                    <th>MEETING ATTENDANCE</td>
                    <th>LEGEND</td>
                </tr>
                <tr>
                    <td width="66.67%">
                    <?php $colours = array("e53935", "d81b60", "8e24aa", "5e35b1", "3949ab", "1e88e5", "039be5", "00acc1", "00897b", "43a047", "7cb342", "c0ca33", "fdd835", "ffb300", "fb8c00", "f4511e", "6d4c41", "757575", "546e7a");
                            $even_distribute = 100 / ($totalmeetings + ($totalmeetings + 1));
                        $spacing = $even_distribute;
                        
                        echo "<style>";
                        echo ".report-bar { width: $even_distribute%; }";
                        $i = 1;
                        foreach ($meetings_array as $meeting) {
                            $meeting_date = $meeting["TotalAttendance_Date"];
                            $average_meeting_attendances->execute();
                            $average_meeting_attendance = $average_meeting_attendances->get_result()->fetch_assoc();
                            $value = round($average_meeting_attendance["AVG(ap)"] * 100);
                            echo ".report-bar.bar$i {";
                                echo "background: #$colours[$i];";
                                echo "left: $spacing%;";
                                echo "height: $value%;";
                            echo "}";

                            echo ".report-bar.bar$i:before {";
                                echo "content: '" . $value . "%';";
                            echo "}";

                            echo ".legend-icon.bar$i {";
                                echo "background-color: #$colours[$i];";
                            echo "}";

                            $i++;
                            $spacing += ($even_distribute * 2);
                        }
                        echo "</style>"; ?>
                        <div class="report-barchart" style="height: 25vh">
                            <?php for ($x=1; $x <= $i; $x++) { ?>
                                <div class="report-bar bar<?php echo $x ?>"></div>
                            <?php } ?>
                            <!-- https://codepen.io/dxdc100xp/pen/WwMQwE?editors=0100 -->
                        </div>
                    </td>
                    <td width="33.33%" style="padding-left: 20px">
                        <?php
                        $x = 1;
                        foreach ($meetings_array as $meeting) { ?>
                        <div>
                            <div class="legend-icon bar<?php echo $x ?>"></div>
                            <span class="legend-detail"><?php echo date("d/m/Y", strtotime($meeting["TotalAttendance_Date"])) ?></span>
                        </div>
                        <?php $x++; } ?>
                    </td>
                </tr>
            </table>
    
            <div class="section-title">ATTENDANCE</div>
            <table>
                <thead>
                    <tr>
                        <th width="12.5%">TP NO.</th>
                        <th width="22.5%">NAME</th>
                        <?php foreach ($meetings_array as $meeting) { ?>
                        <th><?php echo date("d/m", strtotime($meeting["TotalAttendance_Date"])); ?></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($members_array as $member) {
                    ?>
                    <tr>
                        <td><?php echo $member["User_ID"] ?></td>
                        <td><?php echo strtoupper($member["User_FirstName"]) . " " . strtoupper($member["User_LastName"]) ?></td>
                        <?php foreach ($meetings_array as $meeting) {
                            $member_user_id = $member["User_ID"];
                            $meeting_date = $meeting["TotalAttendance_Date"];
                            $prepared_attendance->execute();
                            $attendances = $prepared_attendance->get_result(); ?>
                        <td>
                            <?php
                            if ($attendance = $attendances->fetch_assoc()) {
                                if ($attendance["Attendance_Presence"] == 1) {
                                    echo "P";
                                } else {
                                    echo "A";
                                }
                            } else {
                                echo "-";
                            }?></td>
                        <?php } ?>
                    </tr>
                    <?php }
                    $prepared_attendance->close() ?>
                </tbody>
            </table>
        </div>
    <script>window.print()</script>
    </body>
    </html>
<?php
} else {
    header("Location: ../nopermission.php");
}
?>
