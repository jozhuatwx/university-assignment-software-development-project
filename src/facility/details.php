<?php
include_once("../server.php");

if (isLoggedIn()) {
    if (isset($_POST["request_id"])) {
        $request_id = $link->real_escape_string($_POST["request_id"]);
    }
    
    function approvalColour($approval) {
        switch ($approval) {
            case "Pending":
                echo "yellow darken-2";
                break;
            case "Approved":
                echo "green";
                break;
            case "Rejected":
                echo "red";
                break;
        }
    }
    
    // Retrieve Transportation
    $transports_sql = "SELECT * FROM transportations";
    $transports = $link->query($transports_sql);
    
    // Identify what request
    if ($request_id[0] == "R") {
        if (isCommittee()) {
            // Get Club Role
            $club_role_sql =    "SELECT users_clubs.UsersClub_Role
                                FROM users_clubs
                                INNER JOIN clubs ON users_clubs.Club_ID=clubs.Club_ID
                                INNER JOIN rooms_requests ON rooms_requests.Club_ID=clubs.Club_ID
                                WHERE users_clubs.User_ID='$user_id' AND (users_clubs.UsersClub_Approval='Approved' OR users_clubs.UsersClub_Approval='Terminate')
                                AND rooms_requests.RoomsRequest_ID='$request_id'";
            if ($club_role_detail = $link->query($club_role_sql)) {
                $club_role_detail = $club_role_detail->fetch_assoc();
                $club_role = $club_role_detail["UsersClub_Role"];
            }
        }
        if (isEventCommittee()) {
            // Get Event Role
            $event_role_sql =   "SELECT users_events.UsersEvent_Role
                                FROM users_events
                                INNER JOIN events ON users_events.Event_ID=events.Event_ID
                                INNER JOIN rooms_requests ON rooms_requests.Event_ID=events.Event_ID
                                WHERE users_events.User_ID='$user_id' AND (users_events.UsersEvent_Approval='Approved' OR users_events.UsersEvent_Approval='Terminate')
                                AND rooms_requests.RoomsRequest_ID='$request_id'";
            if ($event_role_detail = $link->query($event_role_sql)) {
                $event_role_detail = $event_role_detail->fetch_assoc();
                $event_role = $event_role_detail["UsersEvent_Role"];
            }
        }

        if ((isset($club_role) && $club_role == 2) || (isset($event_role) && ($event_role == 2 || $event_role == 3)) || isAdmin()) {
            // Retrieve request information
            $room_request_sql = "SELECT rooms_requests.RoomsRequest_ID, rooms_requests.RoomsRequest_Description, rooms_requests.RoomsRequest_StartDateTime, rooms_requests.RoomsRequest_EndDateTime, rooms_requests.RoomsRequest_NumberofAttendees, rooms_requests.RoomsRequest_Approval, users.User_ID, users.User_FirstName, users.User_LastName, users.User_ContactNumber1, users.User_EmailAddress1, rooms.Room_ID, rooms.Room_Name, clubs.Club_ID, clubs.Club_Name, events.Event_ID, events.Event_Name
                                FROM rooms_requests
                                LEFT JOIN clubs ON rooms_requests.Club_ID=clubs.Club_ID
                                LEFT JOIN events ON rooms_requests.Event_ID=events.Event_ID
                                INNER JOIN users ON rooms_requests.User_ID=users.User_ID
                                INNER JOIN rooms ON rooms_requests.Room_ID=rooms.Room_ID
                                WHERE rooms_requests.RoomsRequest_ID='$request_id'";
            $room_request = $link->query($room_request_sql)->fetch_assoc();
            $approval = $room_request["RoomsRequest_Approval"];
            if ($approval != "Pending") {
                $disabled = true;
            } else {
                $disabled = false;
            }
            if (isAdmin()) {
                $disabled = true;
                if ($room_request["RoomsRequest_Approval"] != "Pending") {
                    $action_disabled = true;
                } else {
                    $action_disabled = false;
                }
            }
        ?>
        <ul class="collapsible">
            <li id="rform" class="active">
                <!-- Room -->
                <div class="collapsible-header"><i class="material-icons">meeting_room</i>Room / Auditorium
                    <span class="new badge <?php approvalColour($approval) ?>" data-badge-caption=""><?php echo "Request " . $approval; ?></span>
                </div>
                
                <div class="collapsible-body white">
                    <form onsubmit="event.preventDefault();">
                        <div class="row">
                            <div class="input-field col m6 s12">
                                <select id="rform-name" disabled>
                                    <option selected><?php if (!empty($room_request["Club_Name"])) {echo $room_request["Club_Name"] . " (" . $room_request["Club_ID"] . ")" ;} elseif (!empty($room_request["Event_Name"])) {echo $room_request["Event_Name"] . " (" . $room_request["Event_ID"] . ")";} ?></option>
                                </select>
                                <label>Club / Event</label>
                            </div>
                        </div>
        
                        <div class="row">
                            <div class="input-field col s12">
                                <textarea <?php if ($disabled) { echo "disabled"; } ?> id="rform-description" class="materialize-textarea validate" data-length="65535" required><?php echo $room_request["RoomsRequest_Description"] ?></textarea>
                                <label for="rform-description" class="active">Description</label>
                            </div>
                        </div>
        
                        <div class="row" onchange="availableRooms('<?php echo $room_request['RoomsRequest_ID'] ?>')">
                            <div class="input-field col m6 s12">
                                <input <?php if ($disabled) { echo "disabled"; } ?> id="rform-startdate" type="date" class="datepicker validate" value="<?php echo date("Y-m-d", strtotime($room_request["RoomsRequest_StartDateTime"])) ?>" min="<?php echo date("Y-m-d", strtotime("+1 week")) ?>" required>
                                <label for="rform-startdate" class="active">Start Date</label>
                            </div>
                            <div class="input-field col m6 s12">
                                <input <?php if ($disabled) { echo "disabled"; } ?> id="rform-enddate" type="date" class="datepicker validate" value="<?php echo date("Y-m-d", strtotime($room_request["RoomsRequest_EndDateTime"])) ?>" min="<?php echo date("Y-m-d", strtotime("+1 week")) ?>" required>
                                <label for="rform-enddate" class="active">End Date</label>
                            </div>
                        </div>
        
                        <div class="row" onchange="availableRooms('<?php echo $room_request['RoomsRequest_ID'] ?>')">
                            <div class="input-field col m6 s12">
                                <input <?php if ($disabled) { echo "disabled"; } ?> id="rform-starttime" type="time" class="timepicker validate" value="<?php echo date("H:i", strtotime($room_request["RoomsRequest_StartDateTime"])) ?>" required>
                                <label for="rform-starttime" class="active">Start Time</label>
                            </div>
                            <div class="input-field col m6 s12">
                                <input <?php if ($disabled) { echo "disabled"; } ?> id="rform-endtime" type="time" class="timepicker validate" value="<?php echo date("H:i", strtotime($room_request["RoomsRequest_EndDateTime"])) ?>" required>
                                <label for="rform-endtime" class="active">End Time</label>
                            </div>
                        </div>
        
                        <div class="row">
                            <div class="input-field col m6 s12">
                                <input <?php if ($disabled) { echo "disabled"; } ?> id="rform-noattendees" type="number" value="<?php echo $room_request["RoomsRequest_NumberofAttendees"] ?>" min="3" max="999" required>
                                <label for="rform-noattendees" class="active">Number of Attendees</label>
                            </div>
                        </div>
        
                        <div class="row">
                            <div id="rform-room-wrapper" class="input-field col m6 s12">
                                <select id="rform-room" <?php if ($disabled) { echo "disabled"; } ?> required>
                                    <option value="<?php echo $room_request["Room_ID"] ?>"><?php echo $room_request["Room_Name"] ?></option>
                                    <?php
                                    $start_datetime = $room_request["RoomsRequest_StartDateTime"];
                                    $start_endtime = $room_request["RoomsRequest_EndDateTime"];
                                    $rooms_request_id = $room_request["RoomsRequest_ID"];
                                    $available_rooms_sql =  "SELECT Room_ID, Room_Name
                                                            FROM rooms
                                                            WHERE NOT Room_ID IN (
                                                                SELECT Room_ID FROM rooms_requests
                                                                WHERE (('$start_datetime' >= RoomsRequest_StartDateTime AND '$start_datetime' < RoomsRequest_EndDateTime)
                                                                OR ('$end_datetime' > RoomsRequest_StartDateTime AND '$end_datetime' <= RoomsRequest_EndDateTime)
                                                                OR ('$start_datetime' <= RoomsRequest_StartDateTime AND '$end_datetime' >= RoomsRequest_EndDateTime))
                                                                AND NOT RoomsRequest_Approval='Rejected'
                                                            )";
                                    $available_rooms = $link->query($available_rooms_sql);
                                    
                                    while ($available_room = $available_rooms->fetch_assoc()) {
                                        echo "<option value='" . $available_room['Room_ID'] . "'>" . $available_room["Room_Name"] . "</option>";
                                    }
                                    ?>
                                </select>
                                <label>Select Room</label>
                            </div>
                        </div>
        
                        <?php if (isAdmin()) { ?>
                        <span class="section-title">Contact Info</span>
                        <div class="row">
                            <div class="input-field col m6 s12">
                                <input disabled type="text" value="<?php echo $room_request["User_FirstName"] . " " . $room_request["User_LastName"] ?>">
                                <label class="active">User Name</label>
                            </div>
                            <div class="input-field col m6 s12">
                                <input disabled type="text" value="<?php echo $room_request["User_ID"] ?>">
                                <label class="active">User ID</label>
                            </div>
                        </div>
        
                        <div class="row">
                            <div class="input-field col m6 s12">
                                <input disabled type="text" value="<?php echo $room_request["User_ContactNumber1"] ?>">
                                <label class="active">Contact Number</label>
                            </div>
                            <div class="input-field col m6 s12">
                                <input disabled type="text" value="<?php echo $room_request["User_EmailAddress1"] ?>">
                                <label class="active">Email Address</label>
                            </div>
                        </div>
        
                        <button class="waves-effect waves-light btn <?php if ($action_disabled) { echo "disabled"; } else { ?>" onclick="facilityRequestConfirm('Approve', '<?php echo $room_request['RoomsRequest_ID'] ?>')<?php } ?>">Approve</button>
                        <button class="waves-effect waves-light btn <?php if ($action_disabled) { echo "disabled"; } else { ?>" onclick="facilityRequestConfirm('Reject', '<?php echo $room_request['RoomsRequest_ID'] ?>')<?php } ?>">Reject</button>                
                        <?php } else { ?>
                        <button type="submit" class="waves-effect waves-light btn <?php if ($disabled) { echo "disabled"; } else { ?>" onclick="roomRequest('UpdateRoomRequest', '<?php echo $room_request['RoomsRequest_ID']; ?>')<?php } ?>">Update Request</button>
                        <button type="submit" class="waves-effect waves-light btn <?php if ($disabled) { echo "disabled"; } else { ?>" onclick="roomRequest('CancelRoomRequest', '<?php echo $room_request['RoomsRequest_ID']; ?>')<?php } ?>">Cancel Request</button>
                        <?php } ?>
                    </form>
                </div>
            </li>
        </ul>
    <?php
        } else {
            include_once("../nopermission.php");
        }
    } elseif ($request_id[0] == "T") {
        if (isCommittee()) {
            // Get Club Role
            $club_role_sql =    "SELECT users_clubs.UsersClub_Role, users_clubs.UsersClub_CommitteeRole
                                FROM users_clubs
                                INNER JOIN clubs ON users_clubs.Club_ID=clubs.Club_ID
                                INNER JOIN transportations_requests ON transportations_requests.Club_ID=clubs.Club_ID
                                WHERE users_clubs.User_ID='$user_id' AND (users_clubs.UsersClub_Approval='Approved' OR users_clubs.UsersClub_Approval='Terminate')
                                AND transportations_requests.TransportationsRequest_ID='$request_id'";
            if ($club_role_detail = $link->query($club_role_sql)) {
                $club_role_detail = $club_role_detail->fetch_assoc();
                $club_role = $club_role_detail["UsersClub_Role"];
            }
        }
        if (isEventCommittee()) {
            // Get Event Role
            $event_role_sql =   "SELECT users_events.UsersEvent_Role
                                FROM users_events
                                INNER JOIN events ON users_events.Event_ID=events.Event_ID
                                INNER JOIN transportations_requests ON transportations_requests.Event_ID=events.Event_ID
                                WHERE users_events.User_ID='$user_id' AND (users_events.UsersEvent_Approval='Approved' OR users_events.UsersEvent_Approval='Terminate')
                                AND transportations_requests.TransportationsRequest_ID='$request_id'";
            if ($event_role_detail = $link->query($event_role_sql)) {
                $event_role_detail = $event_role_detail->fetch_assoc();
                $event_role = $event_role_detail["UsersEvent_Role"];
            }
        }

        if ((isset($club_role) && $club_role == 2) || (isset($event_role) && ($event_role == 2 || $event_role == 3)) || isAdmin()) {
            // Retrieve request information
            $transportation_request_sql =   "SELECT transportations_requests.TransportationsRequest_ID, transportations_requests.TransportationsRequest_Description, transportations_requests.TransportationsRequest_DepartureDateTime, transportations_requests.TransportationsRequest_ReturnDateTime, transportations_requests.TransportationsRequest_NumberofAttendees, transportations_requests.TransportationsRequest_Destination, transportations_requests.TransportationsRequest_DepartureSite, transportations_requests.TransportationsRequest_ReturnSite, transportations_requests.TransportationsRequest_Approval, users.User_ID, users.User_FirstName, users.User_LastName, users.User_ContactNumber1, users.User_EmailAddress1, transportations.Transportation_ID, transportations.Transportation_Type, transportations.Transportation_Seats, clubs.Club_ID, clubs.Club_Name, events.Event_ID, events.Event_Name
                                            FROM transportations_requests LEFT JOIN clubs ON transportations_requests.Club_ID=clubs.Club_ID
                                            LEFT JOIN events ON transportations_requests.Event_ID=events.Event_ID
                                            INNER JOIN users ON transportations_requests.User_ID=users.User_ID
                                            INNER JOIN transportations ON transportations_requests.Transportation_ID=transportations.Transportation_ID
                                            WHERE transportations_requests.TransportationsRequest_ID='$request_id'";
            $transportation_request = $link->query($transportation_request_sql)->fetch_assoc();
            $approval = $transportation_request["TransportationsRequest_Approval"];
            if ($approval != "Pending") {
                $disabled = true;
            } else {
                $disabled = false;
            }
            if (isAdmin()) {
                $disabled = true;
                if ($transportation_request["TransportationsRequest_Approval"] != "Pending") {
                    $action_disabled = true;
                } else {
                    $action_disabled = false;
                }
            }
            $transportation_sites = array("APIIT Campus", "APU Campus", "APU Accommodations", "LRT Bukit Jalil"); ?>
            <ul class="collapsible">
                <!-- Transport -->
                <li id="tform" class="active">
                    <div class="collapsible-header"><i class="material-icons">directions_bus</i>Transport
                        <span class="new badge <?php approvalColour($approval) ?>" data-badge-caption=""><?php echo "Request " . $approval; ?></span>
                    </div>
                    
                    <div class="collapsible-body white">
                        <form onsubmit="event.preventDefault();">
                            <div class="row">
                                <div class="input-field col m6 s12">
                                    <select id="tform-name" disabled>
                                        <option selected><?php if (!empty($transportation_request["Club_Name"])) {echo $transportation_request["Club_Name"] . " (" . $transportation_request["Club_ID"] . ")" ;} elseif (!empty($transportation_request["Event_Name"])) {echo $transportation_request["Event_Name"] . " (" . $transportation_request["Event_ID"] . ")";} ?></option>
                                    </select>
                                    <label>Club / Event</label>
                                </div>
                            </div>
            
                            <div class="row">
                                <div class="input-field col s12">
                                    <textarea <?php if ($disabled) { echo "disabled"; } ?> id="tform-description" class="materialize-textarea validate" data-length="65535" required><?php echo $transportation_request['TransportationsRequest_Description'] ?></textarea>
                                    <label for="tform-description" class="active">Description</label>
                                </div>
                            </div>
            
                            <div class="row">
                                <div class="input-field col m6 s12">
                                    <input <?php if ($disabled) { echo "disabled"; } ?> id="tform-noattendees" type="number" value="<?php echo $transportation_request['TransportationsRequest_NumberofAttendees'] ?>" min="3" max="999" required>
                                    <label for=tform-noattendees" class="active">Number of Attendees</label>
                                </div>
                                <div class="input-field col m6 s12">
                                    <select <?php if ($disabled) { echo "disabled"; } ?> required>
                                        <?php while ($transport = $transports->fetch_assoc()) { ?>
                                            <option value="<?php echo $transport['Transportation_ID']; ?>"><?php echo $transport['Transportation_Type'] . " (" . $transport["Transportation_Seats"] . " per " . strtolower($transport["Transportation_Type"]) . ")"; ?></option>
                                        <?php } ?>
                                    </select>
                                    <label>Select Type of Transport</label>
                                </div>
                            </div>
            
                            <div class="row">
                                <div class="input-field col s12">
                                    <textarea <?php if ($disabled) { echo "disabled"; } ?> id="tform-destination" class="materialize-textarea validate" data-length="65535" required><?php echo $transportation_request['TransportationsRequest_Destination'] ?></textarea>
                                    <label for="tform-destination" class="active">Destination</label>
                                </div>
                            </div>
            
            
                            <!-- Departure Details -->
                            <div class="row">
                                <div class="input-field col m6 s12">
                                    <input <?php if ($disabled) { echo "disabled"; } ?> id="tform-startdate" type="text" class="datepicker" value="<?php echo date("Y-m-d", strtotime($transportation_request["TransportationsRequest_DepartureDateTime"])) ?>" min="<?php echo date("Y-m-d", strtotime("+1 week")) ?>" required>
                                    <label for="tform-startdate" class="active">Depature Date</label>
                                </div>
                                <div class="input-field col m6 s12">
                                    <input <?php if ($disabled) { echo "disabled"; } ?> id="tform-starttime" type="text" class="timepicker" value="<?php echo date("H:i", strtotime($transportation_request["TransportationsRequest_DepartureDateTime"])) ?>" required>
                                    <label for="tform-starttime" class="active">Depature Time</label>
                                </div>
                            </div>
            
                            <div class="row">
                                <div id="tform-departuresite" class="input-field col m6 s12">
                                    <select multiple <?php if ($disabled) { echo "disabled"; } ?> required>
                                        <?php $selected_departure_sites = explode(", ", $transportation_request["TransportationsRequest_DepartureSite"]);
                                        foreach ($transportation_sites as $transportation_site) { ?>
                                            <option <?php if (in_array($transportation_site, $selected_departure_sites)) { echo "selected"; } ?>><?php echo $transportation_site ?></option>
                                        <?php } ?>
                                    </select>
                                    <label>Select Departure Site(s)</label>
                                </div>
                            </div>
            
            
                            <!-- Return Details -->
                            <div class="row">
                                <div class="input-field col m6 s12">
                                    <input <?php if ($disabled) { echo "disabled"; } ?> id="tform-enddate" type="text" class="datepicker" value="<?php echo date("Y-m-d", strtotime($transportation_request["TransportationsRequest_ReturnDateTime"])) ?>" min="<?php echo date("Y-m-d", strtotime("+1 week")) ?>" required>
                                    <label for="tform-enddate" class="active">Return Date</label>
                                </div>
                                <div class="input-field col m6 s12">
                                    <input <?php if ($disabled) { echo "disabled"; } ?> id="tform-endtime" type="text" class="timepicker" value="<?php echo date("H:i", strtotime($transportation_request["TransportationsRequest_ReturnDateTime"])) ?>" required>
                                    <label for="tform-endtime" class="active">Return Time</label>
                                </div>
                            </div>
            
                            <div class="row">
                                <div id="tform-returnsite" class="input-field col m6 s12">
                                    <select multiple <?php if ($disabled) { echo "disabled"; } ?> required>
                                        <?php $selected_return_sites = explode(", ", $transportation_request["TransportationsRequest_ReturnSite"]);
                                        foreach ($transportation_sites as $transportation_site) { ?>
                                            <option <?php if (in_array($transportation_site, $selected_return_sites)) { echo "selected"; } ?>><?php echo $transportation_site ?></option>
                                        <?php } ?>
                                    </select>
                                    <label>Select Return Site(s)</label>
                                </div>
                            </div>
            
                            <?php if (isAdmin()) { ?>
                            <span class="section-title">Contact Info</span>
                            <div class="row">
                                <div class="input-field col m6 s12">
                                    <input disabled type="text" value="<?php echo $transportation_request["User_FirstName"] . " " . $transportation_request["User_LastName"] ?>">
                                    <label class="active">User Name</label>
                                </div>
                                <div class="input-field col m6 s12">
                                    <input disabled type="text" value="<?php echo $transportation_request["User_ID"] ?>">
                                    <label class="active">User ID</label>
                                </div>
                            </div>
            
                            <div class="row">
                                <div class="input-field col m6 s12">
                                    <input disabled type="text" value="<?php echo $transportation_request["User_ContactNumber1"] ?>">
                                    <label class="active">Contact Number</label>
                                </div>
                                <div class="input-field col m6 s12">
                                    <input disabled type="text" value="<?php echo $transportation_request["User_EmailAddress1"] ?>">
                                    <label class="active">Email Address</label>
                                </div>
                            </div>
            
                            <button class="waves-effect waves-light btn <?php if ($action_disabled) { echo "disabled"; } else { ?>" onclick="facilityRequestConfirm('Approve', '<?php echo $transportation_request['TransportationsRequest_ID'] ?>')<?php } ?>">Approve</button>
                            <button class="waves-effect waves-light btn <?php if ($action_disabled) { echo "disabled"; } else { ?>" onclick="facilityRequestConfirm('Reject', '<?php echo $transportation_request['TransportationsRequest_ID'] ?>')<?php } ?>">Reject</button>
                            <?php } else { ?>                
                            <button type="submit" class="waves-effect waves-light btn <?php if ($disabled) { echo "disabled"; } else { ?>" onclick="transportationRequest('UpdateTransportationRequest', '<?php echo $transportation_request['TransportationsRequest_ID']; ?>')<?php } ?>">Update Request</button>
                            <button type="submit" class="waves-effect waves-light btn <?php if ($disabled) { echo "disabled"; } else { ?>" onclick="transportationRequest('CancelTransportationRequest', '<?php echo $transportation_request['TransportationsRequest_ID']; ?>')<?php } ?>">Cancel Request</button>
                            <?php } ?>
                        </form>
                    </div>
                </li>
            </ul>
<?php
        } else {
            include_once("../nopermission.php");
        }
    } else {
        include_once("../notfound.php");
    }
} else {
    include_once("../nopermission.php");
}
$link->close();
?>