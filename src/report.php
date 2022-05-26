<?php
include_once("server.php");
if (!isLoggedIn()) {
    header("Location: login.php");
}
if (!isAdmin()) {
    header("Location: nopermission.php");
}
?>
<style>
h6 {
    font-size: 1.1rem;
}
.generate-report .select-wrapper input.select-dropdown {
    color: white;
}

.generate-report .select-wrapper .caret {
    fill: white;
}

.input-field {
    margin: 0;
}

.report-barchart {
    width: 100%;
    height: calc(75vh - 150px);
    position: relative;
    margin-top: 30px
}

.report-barchart:before {
    content: "";
    width: 100%;
    height: 1px;
    background: #f2f2f2;
    position: absolute;
    top: 50%;
}

.report-barchart:after {
    content: "";
    width: 100%;
    height: 50%;
    background: transparent;
    position: absolute;
    top: 25%;
    border-top: 1px solid #f2f2f2;
    border-bottom: 1px solid #f2f2f2;
}

.report-bar {
    position: absolute;
    bottom: 0;
    z-index: 99;
    float: left;
}

.report-bar:before {
    position: absolute;
    width: 43px;
    text-align: center;
    top: -28px;
    font-size: 18px;
    left: calc(50% - 20px);
}

.legend-icon {
    height: 20px;
    width: 20px;
    display: inline-block;
}

.legend-detail {    
    font-size: 1.15rem;
    font-weight: 400;
    padding: 10px 0 0 15px;
    display: inline-block;
}
</style>
<div style="margin: 15px 20px">
    <?php
    // Retrieve Meeting Months (All Time)
    $meeting_months_sql =   "SELECT MONTHNAME(TotalAttendance_Date) AS Month, YEAR(TotalAttendance_Date) AS Year, EXTRACT(YEAR_MONTH FROM TotalAttendance_Date) AS OrderIndex
                            FROM total_attendances
                            GROUP BY Month, Year
                            UNION
                            SELECT MONTHNAME(TransportationsRequest_DepartureDateTime) AS Month, YEAR(TransportationsRequest_DepartureDateTime) AS Year, EXTRACT(YEAR_MONTH FROM TransportationsRequest_DepartureDateTime)
                            FROM transportations_requests
                            GROUP BY Month, Year
                            UNION
                            SELECT MONTHNAME(RoomsRequest_StartDateTime) AS Month, YEAR(RoomsRequest_StartDateTime) AS Year, EXTRACT(YEAR_MONTH FROM RoomsRequest_StartDateTime)
                            FROM rooms_requests
                            GROUP BY Month, Year
                            UNION
                            SELECT MONTHNAME(CURRENT_DATE), YEAR(CURRENT_DATE), EXTRACT(YEAR_MONTH FROM CURRENT_DATE)
                            ORDER BY OrderIndex DESC";
    $meeting_months = $link->query($meeting_months_sql);
    $meeting_months_array = array();
    while ($meeting_month = $meeting_months->fetch_assoc()) {
        $meeting_months_array[] = $meeting_month["Month"] . " " . $meeting_month["Year"];
    }

    // Retrieve Clubs
    $clubs_sql = "SELECT Club_ID, Club_Name FROM clubs ORDER BY Club_Name ASC";
    $clubs_list = $link->query($clubs_sql);
    $clubs = array();
    while ($club = $clubs_list->fetch_assoc()) {
        $clubs[] = $club;
    }

    // Retrieve Events
    $events_sql = "SELECT Event_ID, Event_Name FROM events ORDER BY Event_Name ASC";
    $events = $link->query($events_sql);

    // Retrieve Summary
    // Members Involvement in Club Events
    $prepared_events = $link->prepare("SELECT Event_ID FROM events WHERE Club_ID=? AND Event_Approval='Approved'");
    $prepared_events->bind_param("s", $club_id);

    $prepared_average_tendencies = $link->prepare("SELECT AVG(ap) FROM (
                                                    SELECT AVG(Attendance_Presence) AS ap
                                                    FROM attendances
                                                    INNER JOIN total_attendances ON attendances.TotalAttendance_ID=total_attendances.TotalAttendance_ID
                                                    WHERE Event_ID=? AND YEAR(TotalAttendance_Date)=YEAR(CURRENT_DATE)
                                                    GROUP BY User_ID
                                                ) AS Average");
    $prepared_average_tendencies->bind_param("s", $event_id);
    $club_average_tendencies = array();

    foreach ($clubs as $club) {
        $club_id = $club["Club_ID"];

        $prepared_events->execute();
        $events = $prepared_events->get_result();
        $total_events = $events->num_rows;
        $total_average_tendencies = 0;

        $count = 0;
        if ($total_events > 0) {
            while ($event = $events->fetch_assoc()) {
                $event_id = $event["Event_ID"];
    
                $prepared_average_tendencies->execute();
                $average_tendencies = $prepared_average_tendencies->get_result()->fetch_assoc();
    
                if ($average_tendencies["AVG(ap)"] > 0) {
                    $total_average_tendencies += $average_tendencies["AVG(ap)"];
                    $count++;
                }
            }
            if ($total_average_tendencies > 0) {
                $club_average_tendencies[$club_id] = $total_average_tendencies / $count * 100;
            }
        }
    }
    $highest_involvement = max($club_average_tendencies);
    $highest_involvement_club_id = array_search(max($club_average_tendencies), $club_average_tendencies);
    $highest_involvement_club_name = $clubs[array_search($highest_involvement_club_id, $clubs)]["Club_Name"];

    $prepared_average_tendencies->close();
    $prepared_events->close();

    // Club with Most Events (Local)
    $clubs_local_sql = "SELECT Club_ID, Club_Name FROM clubs WHERE NOT Club_Type='Community'";
    $clubs_local = $link->query($clubs_local_sql);

    $p_events = $link->prepare("SELECT COUNT(DISTINCT Event_ID) AS c FROM events WHERE Club_ID=?");
    $p_events->bind_param("s", $club_id);

    $highest_events_local = 0;
    $highest_events_club_name_local = "";

    while ($club_local = $clubs_local->fetch_assoc()) {
        $club_id = $club_local["Club_ID"];
        $p_events->execute();
        $events_id = $p_events->get_result()->fetch_assoc();
        $noevents = $events_id["c"];
        if ($highest_events_local < $noevents) {
            $highest_events_local = $noevents;
            $highest_events_club_name_local = $club_local["Club_Name"];
        }
    }

    $p_events->close();

    // Club with Most Events (International)
    $clubs_inter_sql = "SELECT Club_ID, Club_Name FROM clubs WHERE Club_Type='Community'";
    $clubs_inter = $link->query($clubs_inter_sql);

    $p_events = $link->prepare("SELECT COUNT(DISTINCT Event_ID) AS c FROM events WHERE Club_ID=?");
    $p_events->bind_param("s", $club_id);

    $highest_events_inter = 0;
    $highest_events_club_name_inter = "";

    while ($club_inter = $clubs_inter->fetch_assoc()) {
        $club_id = $club_inter["Club_ID"];
        $p_events->execute();
        $events_id = $p_events->get_result()->fetch_assoc();
        $noevents = $events_id["c"];
        if ($highest_events_inter < $noevents) {
            $highest_events_inter = $noevents;
            $highest_events_club_name_inter = $club_inter["Club_Name"];
        }
    }

    $p_events->close();

    // Student Involvement in Events
    $noinvolvement_sql = "SELECT COUNT(DISTINCT User_ID) AS c FROM users_events WHERE UsersEvent_Role BETWEEN 2 AND 4";
    $noinvolvement = $link->query($noinvolvement_sql)->fetch_assoc();
    $noinvolvement = $noinvolvement["c"];

    $noadminsadvisors_sql = "SELECT COUNT(DISTINCT User_ID) AS c FROM users_clubs WHERE UsersClub_Role BETWEEN 0 AND 1";
    $noadminsadvisors = $link->query($noadminsadvisors_sql)->fetch_assoc();
    $noadminsadvisors = $noadminsadvisors["c"];

    $nousers_sql = "SELECT COUNT(DISTINCT User_ID) AS c FROM users";
    $nousers = $link->query($nousers_sql)->fetch_assoc();
    $nousers = $nousers["c"];

    $students_involved = ($noinvolvement / ($nousers - $noadminsadvisors)) * 100; ?>
    <!-- Summary -->
    <div class="row">
        <span class="col section-title">Summary</span>
    </div>
    <div class="row">
        <div class="col xl3 l4 m6 s12">
            <div class="card-panel blue-grey darken-1 white-text" style="height: 165px">
                <!-- Highest Member Involvement in Club Events -->
                <h6 style="margin-top: -10px; line-height: 120%">highest member involvement<br>in club events</h6>
                <h3 style="margin: 0 0 10px 0"><?php echo round($highest_involvement, 2) ?><span style="font-size: 2.25rem"> %</span></h3>
                <h6 style="text-align: right; margin: 0"><?php echo $highest_involvement_club_name ?></h6>
            </div>
        </div>
        <div class="col xl3 l4 m6 s12">
            <div class="card-panel blue-grey darken-1 white-text" style="height: 165px">
                <!-- Most Events (Local) -->
                <h6>most events (local)</h6>
                <h3 style="margin: 0 0 10px 0"><?php echo $highest_events_local  ?><span style="font-size: 2.25rem"></span></h3>
                <h6 style="text-align: right; margin: 0"><?php echo $highest_events_club_name_local ?></h6>
            </div>
        </div>
        <div class="col xl3 l4 m6 s12">
            <div class="card-panel blue-grey darken-1 white-text" style="height: 165px">
                <!-- Most Events (International) -->
                <h6>most events (international)</h6>
                <h3 style="margin: 0 0 5px 0"><?php echo $highest_events_inter ?><span style="font-size: 2.25rem"></span></h3>
                <h6 style="text-align: right; margin: 0"><?php echo $highest_events_club_name_inter ?></h6>
            </div>
        </div>
        <div class="col xl3 l4 m6 s12">
            <div class="card-panel blue-grey darken-1 white-text" style="height: 165px">
                <!-- Student Involvement in Events -->
                <h6>student involvement in events</h6>
                <h3 style="margin: 0 0 5px 0"><?php echo round($students_involved, 2) ?><span style="font-size: 2.25rem"> %</span></h3>
            </div>
        </div>
    </div>

    <!-- Reports -->
    <div class="row">
        <span class="col section-title">Reports</span>
    </div>
    <div class="row">
        <div class="col s12">
            <div class="card-panel blue darken-2 white-text">
                <form onsubmit="event.preventDefault(); generateReport()">
                    <div class="row">
                        <div id="selected-report-type" class="col s3 input-field generate-report" onchange="reportType(); updateGraph();">
                            <select class="validate" required>
                                <option>Attendance</option>
                                <option>Overall Club Attendance</option>
                                <option>Member</option>
                                <option>Committee</option>
                                <option>Approved Booking</option>
                            </select>
                        </div>

                        <div id="selected-name" class="col s4 input-field generate-report" onchange="updateGraph();">
                            <select class="validate" required>
                                <optgroup label="Clubs">
                                    <?php foreach ($clubs as $club) { ?>
                                    <option><?php echo $club["Club_Name"] . " (" . $club["Club_ID"] . ")" ?></option>
                                    <?php } ?>
                                </optgroup>
                                <optgroup label="Events">
                                    <?php while ($event = $events->fetch_assoc()) { ?>
                                    <option><?php echo $event["Event_Name"] . " (" . $event["Event_ID"] . ")" ?></option>
                                    <?php } ?>
                                </optgroup>
                            </select>
                        </div>

                        <div id="selected-month" class="col s2 input-field generate-report" onchange="updateGraph();">
                            <select class="validate" required>
                                <?php foreach ($meeting_months_array as $months) { ?>
                                <option <?php if ($months == (date("F") . " " . date("Y"))) {echo "selected";} ?>><?php echo $months ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <button class="waves-effect waves-light btn-flat btn-large white-text right"><i class="material-icons left">print</i>Print</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Graph -->
    <div id="graph" class="row"></div>
</div>