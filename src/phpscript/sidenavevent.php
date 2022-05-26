<?php
@include_once("../server.php");

// Retrieve Events Info
$events_sql =   "SELECT events.Event_ID, events.Event_Name, events.Event_Logo, users_events.UsersEvent_Role
                FROM events
                INNER JOIN users_events ON events.Event_ID=users_events.Event_ID
                WHERE events.Event_EndDateTime>=NOW() AND users_Events.User_ID='$user_id' AND users_events.UsersEvent_Role<=5 AND NOT (Event_Approval='Canceled' OR Event_Approval='Pending' OR Event_Approval='Updating')";
$events = $link->query($events_sql);

$prepared_announcements = $link->prepare("SELECT Announcement_ID FROM announcements WHERE Event_ID=? AND Announcement_DateTime>=(SELECT User_LastOnline FROM users WHERE User_ID=?)");
$prepared_announcements->bind_param("ss", $event_id, $user_id);

$prepared_public_announcements = $link->prepare("SELECT Announcement_ID FROM announcements WHERE Event_ID=? AND Announcement_Public AND Announcement_DateTime>=(SELECT User_LastOnline FROM users WHERE User_ID=?)");
$prepared_public_announcements->bind_param("ss", $event_id, $user_id);

if ($events->num_rows > 0) { ?>
    <!-- My Events -->
    <li><div class="divider"></div></li>
    <li><a class="subheader">My Upcoming Events</a></li>
        <?php while ($event = $events->fetch_assoc()) {
            $event_id = $event["Event_ID"];
            $total_notifcation = 0;
            if ($event["UsersEvent_Role"] <= 4 && !isAdmin()) {
                $prepared_announcements->execute();
                $new_announcements = $prepared_announcements->get_result();
            } else {
                $prepared_public_announcements->execute();
                $new_announcements = $prepared_public_announcements->get_result();
            }
            $total_notifcation += $new_announcements->num_rows;
            if ($event["UsersEvent_Role"] == 2 || $event["UsersEvent_Role"] == 3) {
                $member_sql = "SELECT User_ID FROM users_events WHERE Event_ID='$event_id' AND UsersEvent_Role=4 AND (UsersEvent_Approval='Pending' OR UsersEvent_Approval='Terminate')";
                $member = $link->query($member_sql);
                $total_notifcation += $member->num_rows;
            }
            ?>
        <li>
            <a class="waves-effect" onclick="openPage('event.php', null, '<?php echo $event_id ?>');">
                <img id="navicon-<?php echo $event_id ?>" src="resource/images/eventlogo/<?php echo $event['Event_Logo'] ?>?rnd=<?php echo mt_rand(0, 9999) ?>" class="navclub-icons">
                <?php echo $event["Event_Name"];
                if ($total_notifcation > 0) { ?>
                <span id="notify-<?php echo $event_id ?>" class="new badge red darken-2" data-badge-caption=""><?php echo $total_notifcation ?></span>
                <?php } ?>
            </a>
        </li>
    <?php } ?>
<?php } ?>