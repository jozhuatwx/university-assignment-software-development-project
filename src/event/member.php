<?php
include_once("../server.php");
include_once("../phpscript/event.php");

if ($event_role <= 4 || isAdmin()) {
    if (($event_role == 2 || $event_role == 3) && !isAdmin()) {
        $member_requests_sql =  "SELECT users.User_ID, users.User_FirstName, users.User_LastName, users.User_ContactNumber1, users.User_EmailAddress1, users_events.UsersEvent_Approval
                                FROM users INNER JOIN users_events ON users.User_ID=users_events.User_ID
                                WHERE (users_events.UsersEvent_Approval='Pending' OR users_events.UsersEvent_Approval='Terminate')
                                AND (users_events.UsersEvent_Role BETWEEN 2 AND 4) AND users_events.Event_ID='$event_id'
                                ORDER BY users.User_FirstName ASC";
        $member_requests = $link->query($member_requests_sql); ?>
<!-- Requests -->
<span class="section-title">Membership Requests
    <?php if ($member_requests->num_rows > 0) { ?><span class="new badge yellow darken-2" data-badge-caption="request(s)"><?php echo $member_requests->num_rows ?></span><?php } ?>
</span>

<div class="row">
    <?php while ($member_request = $member_requests->fetch_assoc()) { ?>
    <div class="col l4 m6 s12">
        <div class="card blue-grey darken-1">
            <div class="card-content white-text">
                <?php if ($member_request["UsersEvent_Approval"] == "Pending") { ?>
                <p>Volunteer</p>
                <?php } elseif ($member_request["UsersEvent_Approval"] == "Terminate") { ?>
                <p>Leave</p>
                <?php } ?>

                <span class="card-title"><?php echo $member_request["User_FirstName"] . " " . $member_request["User_LastName"]; ?></span>
                <p><?php echo $member_request["User_ID"] ?></p>
                <p><?php echo $member_request["User_ContactNumber1"] ?></p>
                <p><?php if ($member_request["User_EmailAddress1"] != null) { echo $member_request["User_EmailAddress1"]; } else { echo $member_request["User_ID"] . "@mail.apu.edu.my"; } ?></p>
            </div>
            <?php if (($event_role == 3 || $event_role == 2) && $member_request["User_ID"] != $user_id) { ?>
            <div class="card-action">
                <a onclick="membershipConfirm('Approve', '<?php echo $member_request['User_ID'] ?>', null, '<?php echo $event_id ?>')">Approve</a>
                <a onclick="membershipConfirm('Reject', '<?php echo $member_request['User_ID'] ?>', null, '<?php echo $event_id ?>')">Reject</a>
            </div>
            <?php } elseif ($member_request["User_ID"] == $user_id) { ?>
            <div class="card-action">
                <a class="grey-text" onclick="M.toast({html: 'Cannot approve own request'})">Approve</a>
                <a class="grey-text" onclick="openSubpage('event/leave.php', 'Leave Event', null, '<?php echo $event_id ?>'); M.toast({html: 'To cancel this request, click Cancel Request'})">Reject</a>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>
<?php
if ($member_requests->num_rows == 0) { ?>
    <div class="col s12" style="text-align: center; margin: 15px 0">No membership request</div>
<?php }
}

// Retrieve member list
$members_sql =  "SELECT users.User_ID, users.User_FirstName, users.User_LastName, users.User_ContactNumber1, users.User_EmailAddress1, users_events.UsersEvent_Approval, users_events.UsersEvent_Role
                FROM users INNER JOIN users_events ON users.User_ID=users_events.User_ID
                WHERE (users_events.UsersEvent_Approval='Approved' OR users_events.UsersEvent_Approval='Terminate')
                AND (users_events.UsersEvent_Role BETWEEN 2 AND 4) AND users_events.Event_ID='$event_id'
                ORDER BY users.User_FirstName ASC";
$members = $link->query($members_sql);
?>

<!-- Member List -->
<div class="row">
    <div class="col s12">
        <span class="section-title">Members</span>
        <?php if ($event_role <= 3 || isAdmin()) { ?>
        <a href="report/memberlist.php?event_id=<?php echo $event_id ?>" target="_blank" class="waves-effect waves-dark btn-flat right"><i class="material-icons left">print</i>Print List</a>
        <?php } ?>
    </div>
</div>
<div class="card-panel blue-grey darken-1 white-text">
    <table class="highlight">
        <thead>
            <tr>
                <th>TP Number</th>
                <th>Name</th>
                <?php if ($event_role <= 3 || isAdmin()) { ?>
                <th>Contact Number</th>
                <th>Email Address</th>
                <?php if (!isAdmin() && ($event_role == 2 || $event_role == 3)) { ?>
                <th>Action</th>
                <?php }} ?>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($event_role <= 3 || isAdmin()) {
                while ($member = $members->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo strtoupper($member["User_ID"]) ?></td>
                    <td><?php echo $member["User_FirstName"] . " " . $member["User_LastName"] ?></td>
                    <td><?php echo $member["User_ContactNumber1"] ?></td>
                    <td><?php echo $member["User_EmailAddress1"] ?></td>
                    <td>
                    <?php if (!isAdmin() && ($event_role == 2 || $event_role == 3) && $member["UsersEvent_Role"] != 2 && $member["UsersEvent_Role"] != 3) { ?>
                        <a class="waves-effect waves-light btn-flat white-text" onclick="membershipConfirm('Sack', '<?php echo $member['User_ID'] ?>', null, '<?php echo $event_id ?>')"><i class="material-icons left">exit_to_app</i>Sack</a>
                    <?php } elseif (!isAdmin() && ($event_role == 2 || $event_role == 3) && ($member["UsersEvent_Role"] == 2 || $member["UsersEvent_Role"] == 3)) { ?>
                        <a class="waves-effect waves-light btn-flat grey-text" onclick="M.toast({html: 'Please remove member from committee first'})"><i class="material-icons left">exit_to_app</i>Sack</a>
                    <?php } ?>
                    </td>
                </tr>
                <?php }
            } else {
                while ($member = $members->fetch_assoc()) { ?>                
                <tr>
                    <td><?php echo strtoupper($member["User_ID"]) ?></td>
                    <td><?php echo $member["User_FirstName"] . " " . $member["User_LastName"]; ?></td>
                </tr>
                <?php }
                }?>
        </tbody>
    </table>
</div>
<?php } else {
    include_once("../nopermission.php");
}
$link->close();
?>