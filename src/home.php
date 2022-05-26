<?php
include_once("server.php");
if (!isLoggedIn()) {
    header("Location: login.php");
}

// Retrieve Clubs
$clubs_sql = "SELECT Club_ID FROM users_clubs WHERE User_ID='$user_id' AND UsersClub_Approval='Approved'";
$clubs = $link->query($clubs_sql);
$events_sql = "SELECT Event_ID FROM users_events WHERE User_ID='$user_id' AND UsersEvent_Approval='Approved'";
$events = $link->query($events_sql);

// Retrieve Announcements
$announcements_sql =    "SELECT Announcement_ID, Announcement_Title, Announcement_Statement, Announcement_DateTime, Announcement_Public, announcements.Club_ID, clubs.Club_Name, clubs.Club_Logo, announcements.Event_ID, events.Event_Name, events.Event_Logo
                        FROM announcements
                        LEFT JOIN clubs ON announcements.Club_ID=clubs.Club_ID
                        LEFT JOIN events ON announcements.Event_ID=events.Event_ID
                        WHERE (announcements.Club_ID IS NULL AND Announcement_Public = 1)";

while ($club = $clubs->fetch_assoc()) {
    $club_id = $club["Club_ID"];
    $announcements_sql .= " OR announcements.Club_ID='$club_id'";
}
while ($event = $events->fetch_assoc()) {
    $event_id = $event["Event_ID"];
    $announcements_sql .= " OR announcements.Event_ID='$event_id'";
}
$announcements_sql .= " GROUP BY Announcement_ID ORDER BY Announcement_DateTime DESC";
$announcements = $link->query($announcements_sql);

$prepared_club_role = $link->prepare("SELECT UsersClub_Role FROM users_clubs WHERE User_ID=? AND Club_ID=?");
$prepared_club_role->bind_param("ss", $user_id, $club_id);

$prepared_event_role = $link->prepare("SELECT UsersEvent_Role FROM users_events WHERE User_ID=? AND Event_ID=?");
$prepared_event_role->bind_param("ss", $user_id, $event_id);
?>

<div class="row">
    <h4 class="col offset-xl2 offset-l1 offset-m1">Announcements</h4>
</div>

<?php if (isAdmin()) { ?>
<!-- Post -->
<div class="row">
    <div class="col xl8 offset-xl2 l10 offset-l1 m10 offset-m1 s12">
        <div class="card grey lighten-5">
            <form onsubmit="event.preventDefault(); announcement('Post', null, null, null, 'home');">
                <div class="card-content black-text">
                    <span class="card-title">Post Announcement</span>
                    <div class="row">
                        <div class="input-field col l8 m7 s6">
                            <input id="announcement-title" type="text" class="validate" maxlength="255" required>
                            <label for="announcement-title">Title</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <textarea id="announcement-statement" class="materialize-textarea validate" data-length="65535" required></textarea>
                            <label for="announcement-statement">Content</label>
                        </div>
                    </div>
                </div>

                <div class="card-action">
                    <button class="link" type="submit">Post</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php } ?>

<!-- Announcements -->
<?php 
if ($announcements->num_rows > 0) {
while ($announcement = $announcements->fetch_assoc()) { ?>
<div class="row">
    <div class="col xl8 offset-xl2 l10 offset-l1 m10 offset-m1 s12">
        <div class="card blue-grey darken-1">
            <form onsubmit="event.preventDefault();">
                <div class="card-content white-text">
                    <?php
                    $authorised = false;

                    if ($announcement["Club_ID"] && !isAdmin()) {
                        $club_id = $announcement["Club_ID"];
                        $prepared_club_role->execute();
                        $club_roles = $prepared_club_role->get_result()->fetch_assoc();
                        $club_role = $club_roles["UsersClub_Role"];

                        if ($club_role <= 2 && !empty($club_role)) {
                            $authorised = true;
                        }
                    } elseif ($announcement["Event_ID"] && !isAdmin()) {
                        $event_id = $announcement["Event_ID"];
                        $prepared_event_role->execute();
                        $event_roles = $prepared_event_role->get_result()->fetch_assoc();
                        $event_role = $event_roles["UsersEvent_Role"];

                        if ($event_role <= 3 && !empty($event_role)) {
                            $authorised = true;
                        }
                    } elseif (!isset($announcement["Club_ID"]) && !isset($announcement["Event_ID"]) && isAdmin()) {
                        $authorised = true;
                    }
                    if ($authorised) { ?>
                    <div class="row">
                        <div class="input-field col l8 m7 s6">
                            <input id="title-<?php echo $announcement['Announcement_ID'] ?>" type="text" class="white-text validate" value="<?php echo $announcement['Announcement_Title'] ?>" maxlength="255" required>
                            <label class="active" for="title-<?php echo $announcement['Announcement_ID'] ?>">Title</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <textarea id="statement-<?php echo $announcement['Announcement_ID'] ?>" class="materialize-textarea white-text validate" data-length="65535" required><?php echo $announcement["Announcement_Statement"] ?></textarea>
                            <label class="active" for="statement-<?php echo $announcement['Announcement_ID'] ?>">Content</label>
                        </div>
                    </div>
                    <?php if (!isAdmin()) { ?>
                    <label>
                        <input id="public-<?php echo $announcement['Announcement_ID'] ?>" type="checkbox" class="filled-in" <?php if ($announcement['Announcement_Public'] == 1) {echo "checked";} ?>/>
                        <span>Make Public</span>
                    </label>
                    <?php } ?>
                    <?php } else {
                        if (isset($announcement["Club_ID"]) || isset($announcement["Event_ID"])) { ?>
                    <span class="announcement-name link" onclick="<?php if (!empty($announcement["Club_ID"])) {echo 'openPage(\'club.php\', \''.$announcement["Club_ID"].'\')';} elseif (!empty($announcement["Event_ID"])) {echo 'openPage(\'event.php\', null, \''.$announcement["Event_ID"].'\')';} ?>"><img class="announcement-icons" src="resource/images/<?php if (!empty($announcement["Club_ID"])) {echo "clublogo/" . $announcement["Club_Logo"];} elseif (!empty($announcement["Event_ID"])) {echo "eventlogo/" . $announcement["Event_Logo"];} ?>"><?php if (!empty($announcement["Club_ID"])) {echo $announcement["Club_Name"];} elseif (!empty($announcement["Event_ID"])) {echo $announcement["Event_Name"];} ?></span>
                    <br>
                        <?php } ?>
                    <span class="card-title"><?php echo $announcement["Announcement_Title"] ?></span>
                    <p><?php echo nl2br(preg_replace("~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~", "<a href=\"\\0\" target='_blank'>\\0</a>", $announcement["Announcement_Statement"])) ?></p>
                    <?php } ?>
                </div>
                <?php
                
                if ($authorised) { ?>
                <div class="card-action">
                    <button class="link" type="submit" onclick="announcement('Update', '<?php echo $announcement['Announcement_ID'] ?>', <?php if ($announcement['Club_ID']) { echo '\''. $announcement['Club_ID'] . '\''; } else { echo 'null'; } ?>, <?php if ($announcement['Event_ID']) { echo '\''. $announcement['Event_ID'] . '\''; } else { echo 'null'; } ?>, 'home')">Update</a>
                    <button class="link" type="submit" onclick="announcementConfirm('Delete', '<?php echo $announcement['Announcement_ID'] ?>', <?php if ($announcement['Club_ID']) { echo '\''. $announcement['Club_ID'] . '\''; } else { echo 'null'; } ?>, <?php if ($announcement['Event_ID']) { echo '\''. $announcement['Event_ID'] . '\''; } else { echo 'null'; } ?>, 'home')">Delete</a>
                </div>
                <?php } ?>
            </form>
        </div>
    </div>
</div>
<?php }
} else { ?>
<div class="col s12" style="text-align: center; margin: 30px 0">No announcements</div>
<?php }
$prepared_club_role->close();
$link->close();
?>