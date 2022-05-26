<?php
@include_once("../server.php");
@include_once("../phpscript/event.php");


if ($event_role <= 4 || isAdmin()) {
    // Retrieve Announcements
    $announcements_sql =    "SELECT *
                            FROM announcements
                            WHERE Event_ID='$event_id'
                            ORDER BY Announcement_DateTime DESC";

    $announcements = $link->query($announcements_sql);

    if ($event_role <= 3 && !isAdmin()) { ?>
<span class="section-title">Post Announcement</span>
<!-- Post -->
<div class="row">
    <div class="col s12">
        <div class="card grey lighten-5">
            <form onsubmit="event.preventDefault(); announcement('Post', null, null, '<?php echo $event_id ?>', 'event')">
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
                    <label>
                        <input id="announcement-public" type="checkbox" class="filled-in"/>
                        <span>Make Public</span>
                    </label>
                </div>

                <div class="card-action">
                    <button class="link" type="submit">Post</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php } ?>

<span class="section-title">Announcements</span>
<!-- Announcements -->
<?php
if ($announcements->num_rows > 0) {
while ($announcement = $announcements->fetch_assoc()) { ?>
<div class="row">
    <div class="col s12">
        <div class="card blue-grey darken-1">
            <form onsubmit="event.preventDefault();">
                <div class="card-content white-text">
                    <?php if ($event_role <= 3 && !isAdmin()) { ?>
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
                    <label>
                        <input id="public-<?php echo $announcement['Announcement_ID'] ?>" type="checkbox" class="filled-in" <?php if ($announcement['Announcement_Public'] == 1) {echo "checked";} ?>/>
                        <span>Make Public</span>
                    </label>
                    <?php } else { ?>
                    <span class="card-title"><?php echo $announcement["Announcement_Title"] ?></span>
                    <p><?php echo nl2br(preg_replace("~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~", "<a href=\"\\0\" target='_blank'>\\0</a>", $announcement["Announcement_Statement"])) ?></p>
                    <?php } ?>
                </div>
                <?php if ($event_role <= 3 && !isAdmin()) { ?>
                <div class="card-action">
                    <button class="link" type="submit" onclick="announcement('Update', '<?php echo $announcement['Announcement_ID'] ?>', null, '<?php echo $event_id ?>', 'event')">Update</a>
                    <button class="link" type="submit" onclick="announcementConfirm('Delete', '<?php echo $announcement['Announcement_ID'] ?>', null, '<?php echo $event_id ?>', 'event')">Delete</a>
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
} else {
    include_once("../nopermission.php");
}
?>