<?php
@include_once("../server.php");

if (isLoggedIn()) {
    if (isCommittee() || isEventCommittee()) { ?>

<span class="section-title">Book Facility</span>

<ul class="collapsible">
    <li id="rform" class="active">
        <!-- Room -->
        <div class="collapsible-header"><i class="material-icons">meeting_room</i>Room / Auditorium</div>
        
        <div class="collapsible-body white">
            <form onsubmit="event.preventDefault(); roomRequest('RoomRequest');">
                <div class="row">
                    <div class="input-field col m6 s12">
                        <select id="rform-name" class="validate" required>
                            <?php if (count($clubs_array) > 0) { ?>
                            <optgroup label="Clubs">
                            <?php foreach ($clubs_array as $club) { ?>
                                <option><?php echo $club['Club_Name'] . " (" . $club['Club_ID'] . ")" ?></option>
                            <?php } ?>
                            </optgroup>
                            <?php }
                            if (count($events_array) > 0) { ?>
                            <optgroup label="Events">
                            <?php foreach ($events_array as $event) { ?>
                                <option><?php echo $event['Event_Name'] . " (" . $event['Event_ID'] . ")" ?></option>
                            <?php } ?>
                            </optgroup>
                            <?php } ?>
                        </select>
                        <label>Select Club / Event</label>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col s12">
                        <textarea id="rform-description" class="materialize-textarea validate" data-length="65535" required></textarea>
                        <label for="rform-description">Description</label>
                    </div>
                </div>

                <div class="row" onchange="availableRooms()">
                    <div class="input-field col m6 s12">
                        <input id="rform-startdate" type="date" class="datepicker no-autoinit validate" value="<?php echo date("Y-m-d", strtotime("+1 week")) ?>" min="<?php echo date("Y-m-d", strtotime("+1 week")) ?>" required>
                        <label for="rform-startdate" class="active">Start Date</label>
                    </div>
                    <div class="input-field col m6 s12">
                        <input id="rform-enddate" type="date" class="datepicker no-autoinit validate" value="<?php echo date("Y-m-d", strtotime("+1 week")) ?>" min="<?php echo date("Y-m-d", strtotime("+1 week")) ?>" required>
                        <label for="rform-enddate" class="active">End Date</label>
                    </div>
                </div>
                
                <div class="row" onchange="availableRooms()">
                    <div class="input-field col m6 s12">
                        <input id="rform-starttime" type="time" class="timepicker no-autoinit validate" required>
                        <label for="rform-starttime" class="active">Start Time</label>
                    </div>
                    <div class="input-field col m6 s12">
                        <input id="rform-endtime" type="time" class="timepicker no-autoinit validate" required>
                        <label for="rform-endtime" class="active">End Time</label>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col m6 s12">
                        <input id="rform-noattendees" type="number" class="validate" min="3" max="999" required>
                        <label for="rform-noattendees">Number of Attendees</label>
                    </div>
                </div>

                <div class="row">
                    <div id="rform-room-wrapper" class="input-field col m6 s12">
                        <select id="rform-room" class="validate" required>
                            <option value="" disabled>Select room</option>
                        </select>
                        <label>Select Room</label>
                    </div>
                </div>

                <button class="waves-effect waves-light btn" type="submit">Request</button>
            </div>
        </form>
    </li>

    <!-- Transport -->
    <li id="tform">
        <div class="collapsible-header"><i class="material-icons">directions_bus</i>Transport</div>
        
        <div class="collapsible-body white">
            <form onsubmit="event.preventDefault(); transportationRequest('TransportationRequest');">
                <div class="row">
                    <div class="input-field col m6 s12">
                        <select id="tform-name" class="validate" required>
                            <?php if (count($clubs_array) > 0) { ?>
                            <optgroup label="Clubs">
                            <?php foreach ($clubs_array as $club) { ?>
                                <option><?php echo $club['Club_Name'] . " (" . $club['Club_ID'] . ")" ?></option>
                            <?php } ?>
                            </optgroup>
                            <?php }
                            if (count($events_array) > 0) { ?>
                            <optgroup label="Events">
                            <?php foreach ($events_array as $event) { ?>
                                <option><?php echo $event['Event_Name'] . " (" . $event['Event_ID'] . ")" ?></option>
                            <?php } ?>
                            </optgroup>
                            <?php } ?>
                        </select>
                        <label>Select Club / Event</label>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col s12">
                        <textarea id="tform-description" class="materialize-textarea validate" data-length="65535" required></textarea>
                        <label for="tform-description">Description</label>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col m6 s12">
                        <input id="tform-noattendees" type="number" class="validate" min="3" max="999" required>
                        <label for="tform-noattendees">Number of Attendees</label>
                    </div>
                    <div class="input-field col m6 s12">
                        <select class="validate" required>
                            <?php while ($transport = $transports->fetch_assoc()) { ?>
                                <option value="<?php echo $transport['Transportation_ID']; ?>"><?php echo $transport['Transportation_Type'] . " (" . $transport["Transportation_Seats"] . " per " . strtolower($transport["Transportation_Type"]) . ")"; ?></option>
                            <?php } ?>
                        </select>
                        <label>Select Type of Transport</label>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col s12">
                        <textarea id="tform-destination" class="materialize-textarea validate" data-length="65535" required></textarea>
                        <label for="tform-destination">Destination</label>
                    </div>
                </div>


                <!-- Departure Details -->
                <div class="row">
                    <div class="input-field col m6 s12">
                        <input id="tform-startdate" type="date" class="datepicker no-autoinit validate" value="<?php echo date("Y-m-d", strtotime("+1 week")) ?>" min="<?php echo date("Y-m-d", strtotime("+1 week")) ?>" required>
                        <label for="tform-startdate" class="active">Depature Date</label>
                    </div>
                    <div class="input-field col m6 s12">
                        <input id="tform-starttime" type="time" class="timepicker no-autoinit validate" required">
                        <label for="tform-starttime" class="active">Depature Time</label>
                    </div>
                </div>

                <div class="row">
                    <div id="tform-departuresite" class="input-field col m6 s12">
                        <select multiple class="validate" required>
                            <option>APIIT Campus</option>
                            <option selected>APU Campus</option>
                            <option>APU Accommodations</option>
                            <option>LRT Bukit Jalil</option>
                        </select>
                        <label>Select Departure Site(s)</label>
                    </div>
                </div>


                <!-- Return Details -->
                <div class="row">
                    <div class="input-field col m6 s12">
                        <input id="tform-enddate" type="date" class="datepicker no-autoinit validate" value="<?php echo date("Y-m-d", strtotime("+1 week")) ?>" min="<?php echo date("Y-m-d", strtotime("+1 week")) ?>" required">
                        <label for="tform-enddate" class="active">Return Date</label>
                    </div>
                    <div class="input-field col m6 s12">
                        <input id="tform-endtime" type="time" class="timepicker no-autoinit validate" required">
                        <label for="tform-endtime" class="active">Return Time</label>
                    </div>
                </div>

                <div class="row">
                    <div id="tform-returnsite" class="input-field col m6 s12">
                        <select multiple class="validate" required>
                            <option>APIIT Campus</option>
                            <option selected>APU Campus</option>
                            <option>APU Accommodations</option>
                            <option>LRT Bukit Jalil</option>
                        </select>
                        <label>Select Return Site(s)</label>
                    </div>
                </div>

                <button class="waves-effect waves-light btn" type="submit">Request</button>
            </div>
        </form>
    </li>
</ul>
<?php
    } else {
        include_once("nopermission.php");
    }
}
$link->close();
?>