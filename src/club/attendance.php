<?php
include_once("../server.php");
include_once("../phpscript/club.php");

if ($club_role <= 2 || isAdmin()) { ?>
<style>
.select-wrapper input.select-dropdown {
    height: 2.25rem;
    margin: 0;
}
</style>

<?php
if (isset($_POST["selected_month"]) && !empty($_POST["selected_month"])) {
    $month = date("m", strtotime($_POST["selected_month"]));
    $month_name = date("F", strtotime($_POST["selected_month"]));
    $year = date("Y", strtotime($_POST["selected_month"]));
} else {
    $month = date("m");
    $month_name = date("F");
    $year = date("Y");
}

// Retrieve members
$members_sql = "SELECT users.User_ID, users.User_FirstName, users.User_LastName
                FROM users
                INNER JOIN users_clubs ON users.User_ID=users_clubs.User_ID
                WHERE users_clubs.UsersClub_Role>=2 AND users_clubs.Club_ID='$club_id'
                AND NOT users_clubs.UsersClub_Approval='Pending'
                ORDER BY users.User_FirstName ASC";
$members = $link->query($members_sql);
$members_array = array();
while ($member = $members->fetch_assoc()) {
    $members_array[] = $member;
}

// Retrieve Meeting Dates (This Month)
$meeting_dates_sql =    "SELECT total_attendances.TotalAttendance_Date
                        FROM total_attendances
                        INNER JOIN attendances ON total_attendances.TotalAttendance_ID=attendances.TotalAttendance_ID
                        WHERE attendances.Club_ID='$club_id' AND MONTH(total_attendances.TotalAttendance_Date)=$month AND YEAR(total_attendances.TotalAttendance_Date)=$year
                        AND total_attendances.TotalAttendance_Quantity>0
                        GROUP BY TotalAttendance_Date";
$meeting_dates = $link->query($meeting_dates_sql);
$meeting_dates_array = array();
while ($meeting_date = $meeting_dates->fetch_assoc()) {
    $meeting_dates_array[] = $meeting_date;
}

// Retrieve Meeting Months (All Time)
$meeting_months_sql =   "SELECT MONTHNAME(total_attendances.TotalAttendance_Date) AS Month, YEAR(total_attendances.TotalAttendance_Date) AS Year, EXTRACT(YEAR_MONTH FROM total_attendances.TotalAttendance_Date) AS OrderIndex
                        FROM total_attendances
                        INNER JOIN attendances ON total_attendances.TotalAttendance_ID=attendances.TotalAttendance_ID
                        WHERE attendances.Club_ID='$club_id'
                        GROUP BY Month, Year
                        UNION
                        SELECT MONTHNAME(CURRENT_DATE), YEAR(CURRENT_DATE), EXTRACT(YEAR_MONTH FROM CURRENT_DATE)
                        ORDER BY OrderIndex DESC";
$meeting_months = $link->query($meeting_months_sql);
$meeting_months_array = array();
while ($meeting_month = $meeting_months->fetch_assoc()) {
    $meeting_months_array[] = $meeting_month["Month"] . " " . $meeting_month["Year"];
}

// Prepare Attendance Presence
$prepared_attendance =  $link->prepare("SELECT attendances.Attendance_Presence
                                        FROM attendances
                                        INNER JOIN total_attendances ON attendances.TotalAttendance_ID=total_attendances.TotalAttendance_ID
                                        WHERE attendances.User_ID=? AND attendances.Club_ID=? AND total_attendances.TotalAttendance_Date=?");
$prepared_attendance->bind_param("sss", $member_user_id, $club_id, $meeting_date);

if ($club_role == 2) { ?>
<!-- Take Attendance -->
<span class="section-title">Take Attendance</span>
<ul class="collapsible">
    <li>
        <div class="collapsible-header"><i class="material-icons">person</i> Take Attendance</div>
        <div class="collapsible-body white">
            <table>
                <thead>
                    <tr>
                        <th>TP Number</th>
                        <th>Name</th>
                        <th>Attendance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members_array as $member) { ?>
                    <tr>
                    <td><?php echo $member["User_ID"] ?></td>
                        <td><?php echo $member["User_FirstName"] . " " . $member["User_LastName"] ?></td>
                        <td>
                            <div class="input-field" style="margin: 0" onchange="takeAttendance(this, '<?php echo $member['User_ID'] ?>', '<?php echo $club_id ?>')">
                                <select>
                                    <option selected disabled>Select Presence</option>
                                    <?php
                                    $member_user_id = $member["User_ID"];
                                    $meeting_date = date("Y-m-d");
                                    $prepared_attendance->execute();
                                    $attendances = $prepared_attendance->get_result();
                                    $attendance = $attendances->fetch_assoc();
                                    ?>
                                    <option <?php if ($attendance["Attendance_Presence"] == 1) { echo "selected"; } ?>>Present</option>
                                    <option <?php if ($attendance["Attendance_Presence"] === 0) { echo "selected"; } ?>>Absent</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </li>
</ul>
<?php }
// Retrieve Statistics
$no_of_meeting = count($meeting_dates_array);

$average_no_members_sql =   "SELECT SUM(attendances.Attendance_Presence) AS sp FROM attendances
                            INNER JOIN total_attendances ON attendances.TotalAttendance_ID=total_attendances.TotalAttendance_ID
                            WHERE attendances.Club_ID='$club_id' AND MONTH(total_attendances.TotalAttendance_Date)=$month AND YEAR(total_attendances.TotalAttendance_Date)=$year";
$average_no_members = $link->query($average_no_members_sql)->fetch_assoc();
if ($no_of_meeting > 0) {
    $average_no_member = round($average_no_members["sp"] / $no_of_meeting, 2);
} else {
    $average_no_member = 0;
}

$average_tendencies_sql =  "SELECT AVG(ap) FROM (
                                SELECT AVG(attendances.Attendance_Presence) AS ap
                                FROM attendances
                                INNER JOIN total_attendances ON attendances.TotalAttendance_ID=total_attendances.TotalAttendance_ID
                                WHERE attendances.Club_ID='$club_id' AND MONTH(total_attendances.TotalAttendance_Date)=$month AND YEAR(total_attendances.TotalAttendance_Date)=$year
                                GROUP BY attendances.User_ID
                            ) AS Average";
$average_tendencies = $link->query($average_tendencies_sql)->fetch_assoc();
$average_tendency = round($average_tendencies["AVG(ap)"] * 100, 2);
?>

<!-- Attendance Statistics -->
<div class="row">
    <div class="col s12">
        <span class="section-title">Attendance Statistics</span>
        <a class="waves-effect waves-dark btn-flat right" onclick="attendanceReport('<?php echo $club_id ?>')"><i class="material-icons left">print</i>Print Report</a>
        <div id="selected-month" class="input-field right col m3" style="margin: 0; padding: 0" onchange="selectedAttendanceMonth(this, '<?php echo $club_id ?>')">
            <select>
                <?php foreach ($meeting_months_array as $months) {
                    $selected_month = $month_name . " " . $year; ?>
                <option <?php if ($months == $selected_month) { echo "selected"; } ?>><?php echo $months ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
</div>
<?php ?>
<div class="row">
    <div class="col m4 s12">
        <!-- Number of Meeting -->
        <div class="card-panel orange white-text" style="height: 155px">
            <h6>number of meetings</h6>
            <h2 style="margin: 0 0 10px 0"><?php echo $no_of_meeting; ?></h2>
        </div>
    </div>
    <div class="col m4 s12">
        <!-- Average Number of Members / meeting -->
        <div class="card-panel blue white-text" style="height: 155px">
            <h6 style="margin-top: -7px">average number of<br>members /meeting</h6>
            <h2 style="margin: 0 0 10px 0"><?php echo $average_no_member; ?></h2>
        </div>
    </div>
    <div class="col m4 s12">
        <!-- Average Tendency to Attend -->
        <div class="card-panel green white-text" style="height: 155px">
            <h6>average tendency to attend</h6>
            <h2 style="margin: 0 0 10px 0"><?php echo $average_tendency; ?><span style="font-size: 2.25rem"> %</span></h2>
        </div>
    </div>
</div>

<!-- Attendance List -->
<div class="row">
    <div class="col s12">
        <span class="section-title">Attendance</span>
    </div>
</div>
<div class="card-panel blue-grey darken-1 white-text">
    <table class="highlight">
        <thead>
            <tr>
                <th>TP Number</th>
                <th>Name</th>
                <?php foreach ($meeting_dates_array as $meeting_date) { ?>
                <th><?php echo date("d/m", strtotime($meeting_date["TotalAttendance_Date"])); ?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($members_array as $member) {
            ?>
            <tr>
                <td><?php echo $member["User_ID"] ?></td>
                <td><?php echo $member["User_FirstName"] . " " . $member["User_LastName"] ?></td>
                <?php foreach ($meeting_dates_array as $meeting_date) {
                    $member_user_id = $member["User_ID"];
                    $meeting_date = $meeting_date["TotalAttendance_Date"];
                    $prepared_attendance->execute();
                    $attendances = $prepared_attendance->get_result(); ?>
                <td><i class="material-icons small">
                    <?php
                    if ($attendance = $attendances->fetch_assoc()) {
                        if ($attendance["Attendance_Presence"] == 1) {
                            echo "done";
                        } else {
                            echo "close";
                        }
                    } else {
                        echo "remove";
                    }?>
                </i></td>
                <?php } ?>
            </tr>
            <?php }
            $prepared_attendance->close() ?>
        </tbody>
    </table>
</div>
<?php
} else {
    include_once("../nopermission.php");
}
$link->close();
?>