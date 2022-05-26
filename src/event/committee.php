<?php
include_once("../server.php");
include_once("../phpscript/event.php");

if ($event_role <= 4 || isAdmin()) {
?>
<style>
.card-panel {
    padding: 10px;
}

.card-image img.profile-pic {
    max-height: 175px;
    max-width: 175px;
    margin: 15px auto 0 auto;
    object-fit: contain;
    border-radius: 50%;
}
</style>

<?php if ($event_role == 2 && strtotime($event_endday) >= strtotime("now") && $event_approval != "Canceled") {
    $members_sql =  "SELECT users.User_ID, User_FirstName, User_LastName, UsersEvent_Role, UsersEvent_CommitteeRole, UsersEvent_Approval
                    FROM users
                    INNER JOIN users_events ON users.User_ID=users_events.User_ID
                    WHERE Event_ID='$event_id' AND (UsersEvent_Role BETWEEN 2 AND 4) AND NOT UsersEvent_Approval = 'Pending'";
    $members_list = $link->query($members_sql);
    $members = array();
    while ($member = $members_list->fetch_assoc()) {
        $members[] = $member;
    } ?>
<span class="section-title">Update Committee</span>

<ul class="collapsible">
    <li>
        <div class="collapsible-header"><i class="material-icons">person</i> Update Organiser</div>
        <div class="collapsible-body white">
            <form onsubmit="event.preventDefault(); updateOrganiserConfirm('<?php echo $event_id ?>');">
                <div class="row">
                    <div class="input-field col m6 s12" style="margin: 0">
                        <select id="new-organiser">
                            <?php foreach ($members as $member) {
                                if ($member["UsersEvent_Approval"] == "Approved" && $member["UsersEvent_Role"] != 2) { ?>
                            <option <?php if ($member["UsersEvent_Role"] == 2) { echo "selected"; } ?>><?php echo $member["User_FirstName"] . " " . $member["User_LastName"] . " (" . $member["User_ID"] . ")" ?></option>
                            <?php }
                            } ?>
                        </select>
                        <label>Organiser</label>
                    </div>

                    <button class="waves-effect waves-light btn white-text right"><i class="material-icons left">save</i>Save</button>
                </div>
            </form>
        </div>
    </li>
    <li>
        <div class="collapsible-header"><i class="material-icons">group</i> Update Committee</div>
        <div class="collapsible-body white">
            <table>
                <thead>
                    <tr>
                        <th>TP Number</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Committee Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member) {
                        $member_id = strtoupper($member["User_ID"])?>
                        <tr>
                            <td><?php echo $member_id ?></td>
                            <td><?php echo $member["User_FirstName"] . " " . $member["User_LastName"] ?></td>
                            <td>
                                <div class="input-field" style="margin: 0" <?php if ($member["UsersEvent_Role"] != 2) { echo "onchange=\"updateEventCommittee('$member_id', '$event_id')\""; } ?>>
                                    <select id="role-<?php echo $member_id ?>" <?php if ($member["UsersEvent_Role"] == 2) { echo "disabled"; } ?>>
                                        <option <?php if ($member["UsersEvent_Role"] == 3) { echo "selected"; } ?>>Committee</option>
                                        <option <?php if ($member["UsersEvent_Role"] == 4) { echo "selected"; } ?>>Member</option>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="input-field" style="margin: 0" <?php if ($member["UsersEvent_Role"] != 2) { echo "onfocusout=\"updateEventCommittee('$member_id', '$event_id')\""; } ?>>
                                    <input id="detail-<?php echo $member_id ?>" type="text" class="validate" maxlength="50" placeholder="Committe Role" value="<?php echo $member["UsersEvent_CommitteeRole"] ?>" <?php if ($member["UsersEvent_Role"] == 4) { echo "disabled"; } ?>>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </li>
</ul>
<?php } ?>

<!-- Committee Members -->
<div class="row">
    <div class="col s12">
        <span class="section-title">Committee Members</span>
        <?php if ($event_role <= 3 || isAdmin()) { ?>
        <a href="report/committeelist.php?event_id=<?php echo $event_id ?>" target="_blank" class="waves-effect waves-dark btn-flat right"><i class="material-icons left">print</i>Print List</a>
        <?php } ?>
    </div>
</div>
<?php
$committee_members_sql =    "SELECT users.User_ID, users.User_FirstName, users.User_LastName, users.User_ContactNumber1, users.User_EmailAddress1, users.User_Picture, users_events.UsersEvent_CommitteeRole
                            FROM users
                            INNER JOIN users_events ON users.User_ID=users_events.User_ID
                            WHERE users_events.Event_ID='$event_id' AND (users_events.UsersEvent_Role BETWEEN 2 AND 3)";
$committee_members = $link->query($committee_members_sql);
?>
<div class="row">
<?php while ($committee_member = $committee_members->fetch_assoc()) { ?>
    <div class="col l4 m6 s12">
        <div class="card sticky-action blue-grey darken-1">
            <div class="card-image waves-effect waves-block waves-light activator">
                <img class="profile-pic activator" src="resource/images/profilepicture/<?php echo $committee_member["User_Picture"]; ?>">
            </div>
            <div class="card-content white-text" style="height: 145px">
                <span class="card-title activator"><?php echo $committee_member["User_FirstName"] . " " . $committee_member["User_LastName"] ?><i class="material-icons right">more_vert</i></span>
                <p><?php echo $committee_member["UsersEvent_CommitteeRole"] ?></p>
            </div>
            <div class="card-reveal">
                <span class="card-title grey-text text-darken-4"><?php echo $committee_member["User_FirstName"] . " " . $committee_member["User_LastName"] ?><i class="material-icons right">close</i></span>
                <p><?php echo $committee_member["User_ContactNumber1"] ?></p>
                <p><?php echo $committee_member["User_EmailAddress1"] ?></p>
            </div>
        </div>
    </div>
<?php } ?>
</div>
<?php } else {
    include_once("../nopermission.php");
}
$link->close()
?>