<?php
include_once("../server.php");
include_once("../phpscript/club.php");

if ($club_role == 1) {
    $event_requests_sql = "SELECT events.Event_ID, Event_Name, Event_Description, Event_StartDateTime, Event_EndDateTime, Event_Location, Event_Approval, User_ID FROM events INNER JOIN users_events ON events.Event_ID=users_events.Event_ID WHERE Club_ID='$club_id' AND UsersEvent_Role=2 AND (Event_Approval='Pending' OR Event_Approval='Updating' OR Event_Approval='Canceling')";
    $event_requests = $link->query($event_requests_sql); ?>
<style>
.event-request .card-content {
    height: 250px;
}
</style>
<span class="section-title">Event Request
    <?php if ($event_requests->num_rows > 0) { ?><span class="new badge yellow darken-2" data-badge-caption="request(s)"><?php echo $event_requests->num_rows ?></span><?php } ?>
</span>

<div class="row">
    <?php while ($event_request = $event_requests->fetch_assoc()) { ?>
    <div class="col l5 m6 s12">
        <div class="event-request card sticky-action blue-grey darken-1">
            <div class="card-content white-text">
                <i class="material-icons activator link right">more_vert</i>
                <?php switch ($event_request["Event_Approval"]) {
                    case "Pending":
                        echo "<p>Create Event</p>";
                        break;
                    
                    case "Updating":
                        echo "<p>Update Event</p>";
                        break;

                    case "Canceling":
                        echo "<p>Cancel Event</p>";
                        break;
                } ?>
                <span class="card-title activator"><?php echo $event_request["Event_Name"] ?></span>
                <p style="height: 135px; overflow-y: auto;"><?php echo $event_request["Event_Description"] ?></p>
            </div>
            <div class="card-reveal">
                <span class="card-title grey-text text-darken 4"><?php echo $event_request["Event_Name"] ?><i class="material-icons right">close</i></span>
                <p>Date: <?php echo date("d/m/Y", strtotime($event_request["Event_StartDateTime"])) . " - " . date("d/m/Y", strtotime($event_request["Event_EndDateTime"])) ?></p>
                <p>Time: <?php echo date("h:i A", strtotime($event_request["Event_StartDateTime"])) . " - " . date("h:i A", strtotime($event_request["Event_EndDateTime"])) ?></p>
                <p>Location: <?php echo $event_request["Event_Location"] ?></p>
                <p>Proposed by: <?php echo $event_request["User_ID"] ?></p>
            </div>
            <div class="card-action">
                <a onclick="eventRequestConfirm('Approve', '<?php echo $event_request['Event_ID'] ?>', '<?php echo $club_id ?>')">Approve</a>
                <a onclick="eventRequestConfirm('Reject', '<?php echo $event_request['Event_ID'] ?>', '<?php echo $club_id ?>')">Reject</a>
            </div>
        </div>
    </div>
    <?php }
    if ($event_requests->num_rows == 0) { ?>
        <div class="col s12" style="text-align: center; margin: 15px 0">No event request</div>
    <?php } ?>
</div>
<?php } ?>


<div class="search" style="margin: 0">
    <!-- Search -->
    <div class="row">
        <div class="col l5 m6 s7">
            <div class="row">
                <div class="input-field col s12">
                    <i class="material-icons prefix">search</i>
                    <input type="text" id="search-input" class="autocomplete" onkeyup="searchEvents(this.value, '<?php echo $club_id ?>')">
                    <label for="search-input">Search</label>
                </div>
            </div>
        </div>

        <?php if ($club_role == 2) { ?>
            <a class="waves-effect waves-dark btn-flat right" onclick="openSubpage('club/createevent.php', 'Create Event', '<?php echo $club_id ?>')" style="margin-top: 15px"><i class="material-icons left">add_circle_outline</i>Create Event</a>
        <?php } ?>
    </div>

    <div id="events-list" class="row">
        <?php include_once("../phpscript/allevents.php") ?>
    </div>
</div>