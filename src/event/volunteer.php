<!-- Volunteer Event -->
<?php
@include_once("../server.php");
@include_once("../phpscript/event.php");

// Check if past or cancel
$status_sql = "SELECT Event_Approval, Event_StartDateTime, Event_EndDateTime FROM events WHERE Event_ID='$event_id'";
$statuses = $link->query($status_sql)->fetch_assoc();
$start_datetime = $statuses["Event_StartDateTime"];
$end_datetime = $statuses["Event_EndDateTime"];
$status = $statuses["Event_Approval"];

// Check if requested
$volunteer_request_sql = "SELECT User_ID FROM users_events WHERE UsersEvent_Approval='Pending' AND UsersEvent_Role=4 AND User_ID='$user_id' AND Event_ID='$event_id'";
$volunteer_request = $link->query($volunteer_request_sql)->fetch_assoc();

$follow_request_sql = "SELECT User_ID FROM users_events WHERE UsersEvent_Role=5 AND User_ID='$user_id'";
$follow_request = $link->query($follow_request_sql)->fetch_assoc();


if (($status != "Rejected" && $status != "Canceled") && strtotime($start_datetime) > strtotime("now")) {
    if ($volunteer_request["User_ID"]) { ?>
<div style="text-align: center;">
    <h5 style="margin-bottom: 20px">Your request is being reviewed.</h5>
    <a class="waves-effect waves-light btn" onclick="requestClubEvent('CancelVolunteer', null, '<?php echo $event_id ?>')"><i class="material-icons left">cancel</i>Cancel Request</a>
</div>
<?php } else { ?>
<div style="text-align: center;">
    <h5 style="margin-bottom: 20px">You are not a volunteer of <?php echo $event["Event_Name"] ?>.</h5>
    <?php if (isAdvisor()) { ?>
    <p style="margin-bottom: 20px">Note: To be an advisor for this event, please request the administrator to reassign your event(s).</p>
    <?php } ?>
    <a class="waves-effect waves-light btn <?php if (isAdvisor() || isAdmin()) { echo "disabled"; } ?>" onclick="requestClubEvent('Volunteer', null, '<?php echo $event_id ?>')"><i class="material-icons left">how_to_reg</i>Request to Volunteer</a>
    <?php if ($follow_request["User_ID"]) { ?>
        <a class="waves-effect waves-light btn <?php if (isAdmin()) { echo "disabled"; } ?>" onclick="requestClubEvent('Unfollow', null, '<?php echo $event_id ?>')"><i class="material-icons left">remove_circle_outline</i>Unfollow</a>
    <?php } else { ?>
        <a class="waves-effect waves-light btn <?php if (isAdmin()) { echo "disabled"; } ?>" onclick="requestClubEvent('Follow', null, '<?php echo $event_id ?>')"><i class="material-icons left">playlist_add</i>Follow Event</a>
    <?php } ?>
</div>
<?php }
} elseif ($status == "Canceled") { ?>
    <div style="text-align: center">
        <h5 style="margin-bottom: 20px"><?php echo $event["Event_Name"] ?> has been canceled.</h5>
        <a class="waves-effect waves-light btn disabled"><i class="material-icons left">how_to_reg</i>Request to Volunteer</a>
        <?php if ($follow_request["User_ID"]) { ?>
            <a class="waves-effect waves-light btn disabled"><i class="material-icons left">remove_circle_outline</i>Unfollow</a>
        <?php } else { ?>
            <a class="waves-effect waves-light btn disabled"><i class="material-icons left">playlist_add</i>Follow Event</a>
        <?php } ?>
    </div>
<?php } elseif (strtotime($start_datetime) <= strtotime("now")) {
    if (strtotime($end_datetime) <= strtotime("now")) { ?>
    <div style="text-align: center">
        <h5 style="margin-bottom: 20px"><?php echo $event["Event_Name"] ?> has ended.</h5>
        <a class="waves-effect waves-light btn disabled"><i class="material-icons left">how_to_reg</i>Request to Volunteer</a>
        <?php if ($follow_request["User_ID"]) { ?>
            <a class="waves-effect waves-light btn disabled""><i class="material-icons left">remove_circle_outline</i>Unfollow</a>
        <?php } else { ?>
            <a class="waves-effect waves-light btn disabled"><i class="material-icons left">playlist_add</i>Follow Event</a>
        <?php } ?>
    </div>
    <?php } else {
    ?>
    <div style="text-align: center">
        <h5 style="margin-bottom: 20px"><?php echo $event["Event_Name"] ?> has started.</h5>
        <a class="waves-effect waves-light btn disabled"><i class="material-icons left">how_to_reg</i>Request to Volunteer</a>
        <?php if ($follow_request["User_ID"]) { ?>
            <a class="waves-effect waves-light btn disabled"><i class="material-icons left">remove_circle_outline</i>Unfollow</a>
        <?php } else { ?>
            <a class="waves-effect waves-light btn disabled"><i class="material-icons left">playlist_add</i>Follow Event</a>
        <?php } ?>
    </div>
    <?php }
}
// Retrieve Public Event Announcements
$announcements_sql =    "SELECT *
                        FROM announcements
                        WHERE Event_ID='$event_id'
                        AND Announcement_Public = 1
                        ORDER BY Announcement_DateTime DESC";
$announcements = $link->query($announcements_sql);

if ($announcements->num_rows > 0) { ?>
<span class="section-title">Announcements</span>
<?php
while ($announcement = $announcements->fetch_assoc()) { ?>
<div class="row">
    <div class="col s12">
        <div class="card blue-grey darken-1">
            <div class="card-content white-text">
                <span class="card-title"><?php echo $announcement["Announcement_Title"] ?></span>
                <p><?php echo nl2br($announcement["Announcement_Statement"]) ?></p>
            </div>
        </div>
    </div>
</div>
<?php }
} ?>