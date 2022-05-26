<?php
include_once("../server.php");
include_once("../phpscript/event.php");

$eventrole_sql = "SELECT UsersEvent_Role FROM users_events WHERE User_ID='$user_id' AND Event_ID='$event_id'";
$event_role = $link->query($eventrole_sql)->fetch_assoc();
$event_role = $event_role["UsersEvent_Role"];

$updating_sql = "SELECT Event_Approval FROM events WHERE Old_ID='$event_id'";
$updating = $link->query($updating_sql)->fetch_assoc();

if (($event_role == 2 || $event_role == 3) && $updating["Event_Approval"] != "Updating" && !isAdmin()) {
    $event_sql = "SELECT * FROM events INNER JOIN users_events ON events.Event_ID=users_events.Event_ID WHERE events.Event_ID='$event_id' AND UsersEvent_Role=2";
    $event = $link->query($event_sql)->fetch_assoc();
?>
<!-- Update Event -->
<span class="section-title">Update Event</span>
<div class="row">
    <div class="col s12">
        <div id="form" class="card white">
            <form onsubmit="event.preventDefault(); eventRequestConfirm('UpdateEvent', '<?php echo $event_id ?>', null);">
                <div class="card-content black-text">
                    <span class="card-title">Update Event</span>
                    <div class="row">
                        <div class="input-field col m6 s12">
                            <input id="form-name" type="text" class="validate" maxlength="50" value="<?php echo $event["Event_Name"] ?>" required>
                            <label for="form-name" class="active">Event Name</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <textarea id="form-description" class="materialize-textarea validate" data-length="65535" required><?php echo $event["Event_Description"] ?></textarea>
                            <label for="form-description" class="active">Description</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col m6 s12">
                            <input id="form-startdate" type="date" class="datepicker no-autoinit validate" value="<?php echo date("Y-m-d", strtotime($event["Event_StartDateTime"])) ?>" min="<?php echo date("Y-m-d", strtotime("+1 month")) ?>" required>
                            <label for="form-startdate" class="active">Start Date</label>
                        </div>
                        <div class="input-field col m6 s12">
                            <input id="form-enddate" type="date" class="datepicker no-autoinit validate" value="<?php echo date("Y-m-d", strtotime($event["Event_EndDateTime"])) ?>" min="<?php echo date("Y-m-d", strtotime("+1 month")) ?>" required>
                            <label for="form-enddate" class="active">End Date</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col m6 s12">
                            <input id="form-starttime" type="time" class="timepicker no-autoinit validate" value="<?php echo date("H:i", strtotime($event["Event_StartDateTime"])) ?>" required>
                            <label for="form-starttime" class="active">Start Time</label>
                        </div>
                        <div class="input-field col m6 s12">
                            <input id="form-endtime" type="time" class="timepicker no-autoinit validate" value="<?php echo date("H:i", strtotime($event["Event_EndDateTime"])) ?>" required>
                            <label for="form-endtime" class="active">End Time</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col m6 s12">
                            <input type="text" value="<?php echo $event["User_ID"] ?>" disabled>
                            <label class="active">Organiser</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col m8 s12">
                            <input type="text" value="<?php if ($event["User_ID"] == $user_id) { echo "To update committee, please use the committee tab."; } else { echo "Only person-in-charge can change the committee."; } ?>" disabled>
                            <label class="active">Committee</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <textarea id="form-location" class="materialize-textarea validate" data-length="65535" required><?php echo $event["Event_Location"] ?></textarea>
                            <label for="form-location" class="active">Location</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <button class="waves-effect waves-light btn" type="submit">Update Event</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
} else {
    include_once("../nopermission.php");
}
?>