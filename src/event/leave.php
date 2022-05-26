<!-- Leave Event -->
<?php
include_once("../server.php"); 
include_once("../phpscript/event.php");

// Check if requested
$leave_request_sql = "SELECT User_ID FROM users_events WHERE UsersEvent_Approval='Terminate' AND User_ID='$user_id' AND Event_ID='$event_id'";
$leave_request = $link->query($leave_request_sql)->fetch_assoc();

if ($leave_request["User_ID"]) { ?>
<div style="text-align: center; margin: 100px">
    <h5>Your termination request is being reviewed.</h5>
    <p style="margin-bottom: 20px">Note: You are expected to attend meetings until your termination is approved.</p>
    <a class="waves-effect waves-light btn" onclick="requestClubEvent('CancelLeave', null, '<?php echo $event_id ?>')"><i class="material-icons left">cancel</i>Cancel Request</a>
</div>
<?php } else { ?>
<div style="text-align: center; margin: 100px">
    <h5>Are you sure you want to leave?</h5>
    <p style="margin-bottom: 20px">Note: You are expected to attend meetings until your termination is approved.</p>
    <a class="waves-effect waves-light btn <?php if ($event_role == 2) { echo "disabled"; } ?>"  onclick="requestClubEvent('Leave', null, '<?php echo $event_id ?>')"><i class="material-icons left">exit_to_app</i>Request to Leave Event</a>
</div>
<?php } ?>