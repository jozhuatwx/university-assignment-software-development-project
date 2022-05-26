<?php
include_once("../server.php");
if (!isLoggedIn()) {
    header("Location: ../login.php");
}
if (!isAdmin()) {
    header("Location: ../nopermission.php");
}

$requests_array = array();

if (isset($_GET["selected_month"]) && !empty($_GET["selected_month"])) {
    $month = date("m", strtotime($_GET["selected_month"]));
    $month_name = date("F", strtotime($_GET["selected_month"]));
    $year = date("Y", strtotime($_GET["selected_month"]));
} else {
    $month = date("m");
    $month_name = date("F");
    $year = date("Y");
}

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

array_multisort($starttime, SORT_ASC, $requests_array);
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
        <p>APPROVED BOOKING LIST</p>
        <p>MONTH OF <?php echo strtoupper($month_name) . " " . $year ?></p>
    </div>

    <div class="content">
        <div class="section-title">APPROVED BOOKING LIST</div>
        <?php
        foreach ($requests_array as $request) {
            if ($request[0][0] == "R") { ?>
            <table>
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
        <?php } elseif ($request[0][0] == "T") { ?>
            <table>
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
        <?php }} ?>
    </div>
    <script>window.print()</script>
</body>
</html>