<?php
include_once("../server.php");

if (isAdmin()) {
    if (isset($_POST["club_id"])) {
        $club_id = $link->real_escape_string($_POST["club_id"]);
    }
    if (isset($_POST["event_id"])) {
        $event_id = $link->real_escape_string($_POST["event_id"]);
    }
    if (isset($_POST["report_type"])) {
        $report_type = $link->real_escape_string($_POST["report_type"]);
    }

    if (isset($_POST["selected_month"]) && !empty($_POST["selected_month"])) {
        $month = date("m", strtotime($_POST["selected_month"]));
        $month_name = date("F", strtotime($_POST["selected_month"]));
        $year = date("Y", strtotime($_POST["selected_month"]));
    } else {
        $month = date("m");
        $month_name = date("F");
        $year = date("Y");
    }
    
    if ($report_type == "Attendance" || $report_type == "Overall Club Attendance") {
        $colours = array("e53935", "d81b60", "8e24aa", "5e35b1", "3949ab", "1e88e5", "039be5", "00acc1", "00897b", "43a047", "7cb342", "c0ca33", "fdd835", "ffb300", "fb8c00", "f4511e", "6d4c41", "757575", "546e7a");
        $animation = 0.1; ?>
        <div class="col m9 s12">
            <div class="card-panel" style="min-height: 75vh">

            <?php if ($report_type == "Attendance") {
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
                        echo "animation: showBar$i 1.2s $animation" . "s forwards;";
                    echo "}";

                    echo ".report-bar.bar$i:before {";
                        echo "content: '" . number_format($value, 0) . "%';";
                    echo "}";

                    echo "@keyframes showBar$i {";
                        echo "0% {height: 0px}";
                        echo "100% {height: $value%}";
                    echo "}";

                    echo ".legend-icon.bar$i {";
                        echo "background-color: #$colours[$i];";
                    echo "}";

                    $i++;
                    $spacing += ($even_distribute * 2);
                    $animation += 0.1;
                }
                echo "</style>"; ?>
                <h6>Meeting Attendance</h6>
                <div class="report-barchart">
                    <?php for ($x=1; $x <= $i; $x++) { ?>
                        <div class="report-bar bar<?php echo $x ?>"></div>
                    <?php } ?>
                    <!-- https://codepen.io/dxdc100xp/pen/WwMQwE?editors=0100 -->
                </div>
            </div>
        </div>

        <div class="col m3 s12">
            <div class="card-panel" style="min-height: 375px">
                <h6 style="margin-bottom: 20px">Legend</h6>
                <?php
                $x = 1;
                foreach ($meetings_array as $meeting) { ?>
                <div>
                    <div class="legend-icon bar<?php echo $x ?>"></div>
                    <span class="legend-detail"><?php echo date("d/m/Y", strtotime($meeting["TotalAttendance_Date"])) ?></span>
                </div>
                <?php $x++; } ?>
            </div>
        </div>

            <?php 
            } elseif ($report_type == "Overall Club Attendance") {
                // LIST DOWN ALL CLUBS AND COMPARE
                $clubs_sql = "SELECT Club_ID, Club_Name FROM clubs";
                $clubs = $link->query($clubs_sql);
                $clubs_array = array();
                while ($club = $clubs->fetch_assoc()) {
                    $clubs_array[] = $club;
                }
                $totalclubs = $clubs->num_rows;

                $even_distribute = 100 / ($totalclubs + ($totalclubs + 1));
                $spacing = $even_distribute;

                $prepared_average_attendance = $link->prepare("SELECT AVG(ap) FROM (
                                                                SELECT AVG(attendances.Attendance_Presence) AS ap
                                                                FROM attendances
                                                                INNER JOIN total_attendances ON attendances.TotalAttendance_ID=total_attendances.TotalAttendance_ID
                                                                WHERE attendances.Club_ID=? AND MONTH(total_attendances.TotalAttendance_Date)=? AND YEAR(total_attendances.TotalAttendance_Date)=?
                                                                GROUP BY attendances.User_ID
                                                            ) AS Average");
                $prepared_average_attendance->bind_param("sss", $selected_club_id, $month, $year);
                
                echo "<style>";
                echo ".report-bar { width: $even_distribute%; }";

                $i = 1;
                foreach ($clubs_array as $club) {
                    $selected_club_id = $club["Club_ID"];
                    $prepared_average_attendance->execute();
                    $value = $prepared_average_attendance->get_result()->fetch_assoc();
                    $value = $value["AVG(ap)"] * 100;

                    echo ".report-bar.bar$i {";
                        echo "background: #$colours[$i];";
                        echo "left: $spacing%;";
                        echo "animation: showBar$i 1.2s $animation" . "s forwards;";
                    echo "}";

                    echo ".report-bar.bar$i:before {";
                        echo "content: '" . number_format($value, 0) . "%';";
                    echo "}";

                    echo "@keyframes showBar$i {";
                        echo "0% {height: 0px}";
                        echo "100% {height: $value%}";
                    echo "}";

                    echo ".legend-icon.bar$i {";
                        echo "background-color: #$colours[$i];";
                    echo "}";

                    $i++;
                    $spacing += ($even_distribute * 2);
                    $animation += 0.1;
                }
                echo "</style>"; ?>
                <h6>Overall Club Attendance</h6>
                <div class="report-barchart">
                    <?php for ($x=1; $x <= $i; $x++) { ?>
                        <div class="report-bar bar<?php echo $x ?>"></div>
                    <?php } ?>
                    <!-- https://codepen.io/dxdc100xp/pen/WwMQwE?editors=0100 -->
                </div>
            </div>
        </div>

        <div class="col m3 s12">
            <div class="card-panel" style="min-height: 375px">
                <h6 style="margin-bottom: 20px">Legend</h6>
                <?php
                $x = 1;
                foreach ($clubs_array as $club) { ?>
                <div>
                    <div class="legend-icon bar<?php echo $x ?>"></div>
                    <span class="legend-detail"><?php echo $club["Club_Name"] ?></span>
                </div>
                <?php $x++; } ?>
            </div>
        </div>
            <?php } ?>
<?php } elseif ($report_type == "Member") {
    $members_sql = "SELECT users.User_ID, User_FirstName, User_LastName, User_ContactNumber1, User_ContactNumber2, User_EmailAddress1, User_EmailAddress2
                    FROM users INNER JOIN";
    if (isset($club_id)) {
        $members_sql .= " users_clubs ON users.User_ID=users_clubs.User_ID
                        WHERE (UsersClub_Approval='Approved' OR UsersClub_Approval='Terminate')
                        AND (UsersClub_Role BETWEEN 2 AND 3) AND Club_ID='$club_id'";
    } elseif (isset($event_id)) {
        $members_sql .= " users_events ON users.User_ID=users_events.User_ID
                        WHERE (UsersEvent_Approval='Approved' OR UsersClub_Approval='Terminate')
                        AND (UsersEvent_Role BETWEEN 2 AND 4) AND Event_ID='$event_id'";
    }
    $members_sql .= "ORDER BY users.User_FirstName ASC";
    $members = $link->query($members_sql); ?>
    <div class="row">
        <div class="col s12">
            <div class="card-panel blue-grey darken-1 white-text">
                <table class="highlight">
                    <thead>
                        <tr>
                            <th>TP NO.</th>
                            <th>NAME</th>
                            <th>CONTACT NUMBER 1</th>
                            <th>CONTACT NUMBER 2</th>
                            <th>EMAIL ADDRESS 1</th>
                            <th>EMAIL ADDRESS 2</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            while ($member = $members->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo strtoupper($member["User_ID"]) ?></td>
                                <td><?php echo $member["User_FirstName"] . " " . $member["User_LastName"] ?></td>
                                <td><?php echo $member["User_ContactNumber1"] ?></td>
                                <td><?php echo $member["User_ContactNumber2"] ?></td>
                                <td><?php echo $member["User_EmailAddress1"] ?></td>
                                <td><?php echo $member["User_EmailAddress2"] ?></td>
                            </tr>
                            <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } elseif ($report_type == "Committee") {
    $members_sql =  "SELECT users.User_ID, User_FirstName, User_LastName, User_ContactNumber1, User_EmailAddress1, User_EmailAddress2";
    if (isset($club_id)) {
        $members_sql .= ", UsersClub_CommitteeRoleDetails FROM users
                        INNER JOIN users_clubs ON users.User_ID=users_clubs.User_ID
                        INNER JOIN usersclub_committeeroles ON users_clubs.UsersClub_CommitteeRole=usersclub_committeeroles.UsersClub_CommitteeRole
                        WHERE users_clubs.Club_ID='$club_id' AND users_clubs.UsersClub_Role=2
                        ORDER BY usersclub_committeeroles.UsersClub_CommitteeRole ASC";
    } elseif (isset($event_id)) {
        $members_sql .= ", UsersEvent_CommitteeRole FROM users
                        INNER JOIN users_events ON users.User_ID=users_events.User_ID
                        WHERE users_events.Event_ID='$event_id' AND (UsersEvent_Role BETWEEN 2 AND 3)";
    }
    $members = $link->query($members_sql); ?>
<div class="row">
    <div class="col s12">
        <div class="card-panel blue-grey darken-1 white-text">
            <table class="highlight">
                <thead>
                    <tr>
                        <th>TP NO.</th>
                        <th>NAME</th>
                        <th>ROLE</th>
                        <th>CONTACT NUMBER</th>
                        <th>EMAIL ADDRESS 1</th>
                        <th>EMAIL ADDRESS 2</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        while ($member = $members->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo strtoupper($member["User_ID"]) ?></td>
                            <td><?php echo $member["User_FirstName"] . " " . $member["User_LastName"] ?></td>
                            <td><?php if (isset($member["UsersClub_CommitteeRoleDetails"])) {echo $member["UsersClub_CommitteeRoleDetails"];} elseif (isset($member["UsersEvent_CommitteeRole"])) {echo $member["UsersEvent_CommitteeRole"];} ?></td>
                            <td><?php echo $member["User_ContactNumber1"] ?></td>
                            <td><?php echo $member["User_EmailAddress1"] ?></td>
                            <td><?php echo $member["User_EmailAddress2"] ?></td>
                        </tr>
                        <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php } elseif ($report_type == "Approved Booking") {
    // Retrieve Requests
    $room_requests_sql =    "SELECT rooms_requests.RoomsRequest_ID, rooms_requests.RoomsRequest_Description, rooms_requests.RoomsRequest_StartDateTime, rooms_requests.RoomsRequest_EndDateTime, rooms_requests.RoomsRequest_NumberofAttendees, clubs.Club_Name, events.Event_Name, rooms.Room_Name
                            FROM rooms_requests
                            LEFT JOIN clubs ON rooms_requests.Club_ID=clubs.Club_ID
                            LEFT JOIN events ON rooms_requests.Event_ID=events.Event_ID
                            INNER JOIN rooms ON rooms_requests.Room_ID=rooms.Room_ID
                            WHERE rooms_requests.RoomsRequest_Approval='Approved'
                            AND MONTH(rooms_requests.RoomsRequest_StartDateTime)=$month
                            AND YEAR(rooms_requests.RoomsRequest_StartDateTime)=$year";

    $transport_requests_sql =   "SELECT transportations_requests.TransportationsRequest_ID, transportations_requests.TransportationsRequest_Description, transportations_requests.TransportationsRequest_DepartureDateTime, transportations_requests.TransportationsRequest_ReturnDateTime, transportations_requests.TransportationsRequest_NumberofAttendees, transportations_requests.TransportationsRequest_Destination, transportations_requests.TransportationsRequest_DepartureSite, transportations_requests.TransportationsRequest_ReturnSite, clubs.Club_Name, events.Event_Name, transportations.Transportation_Type
                                FROM transportations_requests
                                LEFT JOIN clubs ON transportations_requests.Club_ID=clubs.Club_ID
                                LEFT JOIN events ON transportations_requests.Event_ID=events.Event_ID
                                INNER JOIN transportations ON transportations_requests.Transportation_ID=transportations.Transportation_ID
                                WHERE transportations_requests.TransportationsRequest_Approval='Approved'
                                AND MONTH(transportations_requests.TransportationsRequest_DepartureDateTime)=$month
                                AND YEAR(transportations_requests.TransportationsRequest_DepartureDateTime)=$year";

    $room_requests = $link->query($room_requests_sql);
    $requests_array = array();
    while ($room_request = $room_requests->fetch_array()) {
        $requests_array[] = $room_request;
    }

    $transport_requests = $link->query($transport_requests_sql);
    while ($transport_request = $transport_requests->fetch_array()) {
        $requests_array[] = $transport_request;
    }

    $starttime = array();
    foreach ($requests_array as $request) {
        $starttime[] = $request[2];
    }

    array_multisort($starttime, SORT_ASC, $requests_array); ?>
    <div class="row">
        <div class="col s12">
            <div class="card-panel blue-grey darken-1 white-text">
            <?php
            foreach ($requests_array as $request) {
                if ($request[0][0] == "R") { ?>
                <table class="highlight">
                    <tr>
                        <th width="25%"><?php if (!empty($request[5])) {echo "CLUB";} elseif (!empty($request[6])) {echo "EVENT";} ?></th>
                        <td width="20%"><?php if (!empty($request[5])) {echo strtoupper($request[5]);} elseif (!empty($request[6])) {echo strtoupper($request[6]);} ?></td>
                        <th width="25%">TYPE</th>
                        <td width="30%">ROOM REQUEST</td>
                    </tr>
                    <tr>
                        <th>DESCRIPTION</th>
                        <td colspan="3"><?php echo strtoupper($request[1]) ?></td>
                    </tr>
                    <tr>
                        <th>START DATE</th>
                        <td><?php echo date("d/m/Y", strtotime($request[2])) ?></td>
                        <th>END DATE</th>
                        <td><?php echo date("d/m/Y", strtotime($request[3])) ?></td>
                    </tr>
                    <tr>
                        <th>START TIME</th>
                        <td><?php echo date("H:i", strtotime($request[2])) ?> HRS</td>
                        <th>END TIME</th>
                        <td><?php echo date("H:i", strtotime($request[3])) ?> HRS</td>
                    </tr>
                    <tr>
                        <th>NO. OF ATTENDEES</th>
                        <td><?php echo $request[4] ?></td>
                        <th>ROOM</th>
                        <td><?php echo $request[7] ?></td>
                    </tr>
                </table>
                <br>
                <br>
            <?php } elseif ($request[0][0] == "T") { ?>
                <table class="highlight">
                    <tr>
                        <th width="25%"><?php if (!empty($request[8])) {echo "CLUB";} elseif (!empty($request[9])) {echo "EVENT";} ?></th>
                        <td width="20%"><?php if (!empty($request[8])) {echo strtoupper($request[8]);} elseif (!empty($request[9])) {echo strtoupper($request[9]);} ?></td>
                        <th width="25%">TYPE</th>
                        <td width="30%">TRANSPORTATION REQUEST</td>
                    </tr>
                    <tr>
                        <th>DESCRIPTION</th>
                        <td colspan="3"><?php echo strtoupper($request[1]) ?></td>
                    </tr>
                    <tr>
                        <th>NO. OF ATTENDEES</th>
                        <td><?php echo $request[4] ?></td>
                        <th>TYPE OF TRANSPORT</th>
                        <td><?php echo strtoupper($request[10]) ?></td>
                    </tr>
                    <tr>
                        <th>DESTINATION</th>
                        <td colspan="3"><?php echo strtoupper($request[5]) ?></td>
                    </tr>
                    <tr>
                        <th>DEPARTURE DATE</th>
                        <td><?php echo date("d/m/Y", strtotime($request[2])) ?></td>
                        <th>DEPARTURE TIME</th>
                        <td><?php echo date("H:i", strtotime($request[2])) ?> HRS</td>
                    </tr>
                    <tr>
                        <th>DEPARTURE SITE</th>
                        <td colspan="3"><?php echo strtoupper($request[6]) ?></td>
                    </tr>
                    <tr>
                        <th>START TIME</th>
                        <td><?php echo date("d/m/Y", strtotime($request[3])) ?></td>
                        <th>END TIME</th>
                        <td><?php echo date("H:i", strtotime($request[3])) ?> HRS</td>
                    </tr>
                    <tr>
                        <th>RETURN SITE</th>
                        <td colspan="3"><?php echo strtoupper($request[7]) ?></td>
                    </tr>
                </table>
                <br>
                <br>
            <?php }
        }
        if (sizeof($requests_array) == 0) { ?>
        <div style="text-align: center">No request</span>
        <?php } ?>
            </div>
        </div>
    </div>
<?php } ?>

<?php } else {
    include_once("../nopermission.php");
} ?>