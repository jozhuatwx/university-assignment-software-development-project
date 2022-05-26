<?php
@include_once("../server.php");

if (isLoggedIn()) {
    if (isAdmin()) {
        // Retrieve Facility Requests
        $room_facility_sql =    "SELECT RoomsRequest_ID, RoomsRequest_Description, RoomsRequest_StartDateTime, RoomsRequest_EndDateTime, RoomsRequest_NumberofAttendees, clubs.Club_Name, events.Event_Name, rooms.Room_Name
                                FROM rooms_requests
                                LEFT JOIN clubs ON rooms_requests.Club_ID=clubs.Club_ID
                                LEFT JOIN events ON rooms_requests.Event_ID=events.Event_ID
                                INNER JOIN rooms ON rooms_requests.Room_ID=rooms.Room_ID
                                WHERE RoomsRequest_Approval='Pending' AND RoomsRequest_StartDateTime >= NOW()";
        $room_requests = $link->query($room_facility_sql);

        $transport_facility_sql =   "SELECT TransportationsRequest_ID, TransportationsRequest_Description, TransportationsRequest_DepartureDateTime, TransportationsRequest_ReturnDateTime, TransportationsRequest_NumberofAttendees, TransportationsRequest_Destination, TransportationsRequest_DepartureSite, TransportationsRequest_ReturnSite, clubs.Club_Name, events.Event_Name, transportations.Transportation_Type
                                    FROM transportations_requests
                                    LEFT JOIN clubs ON transportations_requests.Club_ID=clubs.Club_ID
                                    LEFT JOIN events ON transportations_requests.Event_ID=events.Event_ID
                                    INNER JOIN transportations ON transportations_requests.Transportation_ID=transportations.Transportation_ID
                                    WHERE TransportationsRequest_Approval='Pending' AND TransportationsRequest_DepartureDateTime >= NOW()";
        $transportation_requests = $link->query($transport_facility_sql); ?>

        <span class="col s12 section-title">Room Requests
            <?php if ($room_requests->num_rows > 0) { ?><span class="new badge yellow darken-2" data-badge-caption="request(s)"><?php echo $room_requests->num_rows; ?></span><?php } ?>
        </span>

        <div class="row">
            <?php while ($room_request = $room_requests->fetch_assoc()) {
                if (date("d/m/Y (D)", strtotime($room_request["RoomsRequest_StartDateTime"])) == date("d/m/Y (D)", strtotime($room_request["RoomsRequest_EndDateTime"]))) {
                    $room_request_date = date("d/m/Y (D)", strtotime($room_request["RoomsRequest_StartDateTime"]));
                } else {
                    $room_request_date = date("d/m/Y (D)", strtotime($room_request["RoomsRequest_StartDateTime"])) . " - " . date("d/m/Y (D)", strtotime($room_request["RoomsRequest_EndDateTime"]));
                }
                $room_request_time = date("h:i A", strtotime($room_request["RoomsRequest_StartDateTime"])) . " - " . date("h:i A", strtotime($room_request["RoomsRequest_EndDateTime"]));
            ?>
            <div class="col m6 s12">
                <div class="card blue-grey darken-1">
                    <div class="card-content white-text link" onclick="openSubpage('facility/details.php', null, null, null, '<?php echo $room_request['RoomsRequest_ID'] ?>')">
                        <span class="card-title"><?php echo $room_request["Club_Name"]; ?></span>
                        <p>Description: <?php echo $room_request["RoomsRequest_Description"]; ?></p>
                        <p>Date: <?php echo $room_request_date; ?></p>
                        <p>Time: <?php echo $room_request_time; ?></p>
                        <p>No. of Attendees: <?php echo $room_request["RoomsRequest_NumberofAttendees"]; ?> person(s)</p>
                        <p>Room: <?php echo $room_request["Room_Name"]; ?></p>
                    </div>
                    <div class="card-action">
                        <a onclick="facilityRequestConfirm('Approve', '<?php echo $room_request['RoomsRequest_ID'] ?>')">Approve</a>
                        <a onclick="facilityRequestConfirm('Reject', '<?php echo $room_request['RoomsRequest_ID'] ?>')">Reject</a>
                    </div>
                </div>
            </div>
            <?php } 
            if ($room_requests->num_rows == 0) { ?>
            <div class="col s12" style="text-align: center; margin: 30px 0">No rooms request</div>
            <?php } ?>
        </div>

        <span class="col s12 section-title">Transport Requests
            <?php if ($transportation_requests->num_rows > 0) { ?><span class="new badge yellow darken-2" data-badge-caption="request(s)"><?php echo $transportation_requests->num_rows; ?></span><?php } ?>
        </span>

        <div class="row">
            <?php while ($transportation_request = $transportation_requests->fetch_assoc()) {
                $transport_request_depart = date("d/m/Y, h:i A", strtotime($transportation_request["TransportationsRequest_DepartureDateTime"]));
                $transport_request_return = date("d/m/Y, h:i A", strtotime($transportation_request["TransportationsRequest_ReturnDateTime"]));
            ?>
            <div class="col m6 s12">
                <div class="card blue-grey darken-1">
                    <div class="card-content white-text link" onclick="openSubpage('facility/details.php', null, null, null, '<?php echo $transportation_request['TransportationsRequest_ID'] ?>')">
                        <span class="card-title"><?php echo $transportation_request["Club_Name"]; ?></span>
                        <p>Description: <?php echo $transportation_request["TransportationsRequest_Description"]; ?></p>
                        <p>No. of Attendees: <?php echo $transportation_request["TransportationsRequest_NumberofAttendees"]; ?> person(s)</p>
                        <p>Destination: <?php echo $transportation_request["TransportationsRequest_Destination"]; ?></p>
                        <p>Type: <?php echo $transportation_request["Transportation_Type"]; ?></p>
                        <p>Departure: <?php echo $transport_request_depart; ?></p>
                        <p>Departure Sites: <?php echo $transportation_request["TransportationsRequest_DepartureSite"]; ?></p>
                        <p>Return Time: <?php echo $transport_request_return; ?></p>
                        <p>Return Sites: <?php echo $transportation_request["TransportationsRequest_ReturnSite"]; ?></p>
                    </div>
                    <div class="card-action">
                        <a onclick="facilityRequestConfirm('Approve', '<?php echo $transportation_request['TransportationsRequest_ID'] ?>')">Approve</a>
                        <a onclick="facilityRequestConfirm('Reject', '<?php echo $transportation_request['TransportationsRequest_ID'] ?>')">Reject</a>
                    </div>
                </div>
            </div>
            <?php }
            if ($transportation_requests->num_rows == 0) { ?>
                <div class="col s12" style="text-align: center; margin: 30px 0">No transport request</div>
            <?php } ?>
        </div>
<?php
    } else {
        include_once("nopermission.php");
    }
}
$link->close();
?>