<?php
@include_once("../server.php");

if (isLoggedIn()) {
    if (isset($_POST["club_id"])) {
        $club_id = $link->real_escape_string($_POST["club_id"]);
    }
    if (isset($_POST["search"])) {
        $search = $link->real_escape_string($_POST["search"]);
        $search .= "%";

        // Retrieve Events
        $events_upcoming_sql = "SELECT * FROM events WHERE Event_StartDateTime>=NOW() AND Event_Name LIKE '$search' AND NOT (Event_Approval='Pending' OR Event_Approval='Updating')";
        if (isset($club_id)) {
            $events_upcoming_sql .= " AND Club_ID='$club_id'";
        }
        $events_upcoming_sql .= " ORDER BY Event_StartDateTime ASC, Event_Name ASC";
        $events_upcoming = $link->query($events_upcoming_sql);

        $events_history_sql = "SELECT * FROM events WHERE Event_StartDateTime<NOW() AND Event_Name LIKE '$search' AND NOT (Event_Approval='Pending' OR Event_Approval='Updating')";
        if (isset($club_id)) {
            $events_history_sql .= " AND Club_ID='$club_id'";
        }
        $events_history_sql .= " ORDER BY Event_StartDateTime ASC, Event_Name ASC";
        $events_history = $link->query($events_history_sql);
    } else {
        // Retrieve Events
        $events_upcoming_sql = "SELECT * FROM events WHERE Event_StartDateTime>=NOW() AND NOT (Event_Approval='Pending' OR Event_Approval='Updating')";
        if (isset($club_id)) {
            $events_upcoming_sql .= " AND Club_ID='$club_id'";
        }
        $events_upcoming_sql .= " ORDER BY Event_StartDateTime ASC, Event_Name ASC";
        $events_upcoming = $link->query($events_upcoming_sql);

        $events_history_sql = "SELECT * FROM events WHERE Event_StartDateTime<NOW() AND NOT (Event_Approval='Pending' OR Event_Approval='Updating')";
        if (isset($club_id)) {
            $events_history_sql .= " AND Club_ID='$club_id'";
        }
        $events_history_sql .= " ORDER BY Event_StartDateTime ASC, Event_Name ASC";
        $events_history = $link->query($events_history_sql);
    }

    $prepared_event = $link->prepare("SELECT Event_Approval FROM events WHERE Event_ID=?");
    $prepared_event->bind_param("s", $event_id);
    ?>
    
    <div class="row" style="margin: 0">
        <span class="col section-title">Upcoming Events</span>
    </div>

    <div class="row" style="margin: 0">
        <?php
        $has_result = false;

        while ($event = $events_upcoming->fetch_assoc()) {
            $event_id = $event["Event_ID"];
            $prepared_event->execute();
            $status = $prepared_event->get_result();
            $status = $status->fetch_assoc();
            $status = $status["Event_Approval"];
            $has_result = true;
            $event_starttime = date("h:i A", strtotime($event["Event_StartDateTime"]));
            $event_endtime = date("h:i A", strtotime($event["Event_EndDateTime"]));
        ?>
        <div class="col <?php if (!isset($club_id)) { echo "xl3 l4";} ?> m6 s12">
            <div class="card sticky-action blue-grey darken-1 waves-effect waves-light">
                <div class="card-image activator">
                    <img src="resource/images/eventlogo/<?php echo $event['Event_Logo'] ?>">
                </div>
                <div class="card-content activator">
                    <span class="grey-text text-lighten-4"><?php if ($status == "Canceled") {echo "[Canceled]";} ?></span><span class="card-title activator grey-text text-lighten-4"><?php echo $event["Event_Name"] ?><i class="material-icons right">more_vert</i></span>
                </div>
                <div class="card-reveal">
                    <p class="grey-text text-darken-4"><?php if ($status == "Canceled") {echo "[Canceled]";} ?></p>
                    <span class="card-title grey-text text-darken-4"><?php echo $event["Event_Name"] ?><i class="material-icons right">close</i></span>
                    <p>Description: <?php echo $event["Event_Description"] ?></p>
                    <p>Time: <?php echo $event_starttime . " - " . $event_endtime; ?></p>
                    <p>Location: <?php echo $event["Event_Location"] ?></p>
                </div>
                <div class="card-action blue-grey darken-2">
                    <a onclick="openPage('event.php', null, '<?php echo $event_id ?>');">View Event</a>
                </div>
            </div>
        </div>
    <?php }
    if ($has_result == false) { ?>
        <div class="col s12" style="text-align: center; margin: 15px 0">No events</div>
    <?php } ?>
    </div>

    <div class="row" style="margin: 0">
        <span class="col section-title">Past Events</span>
    </div>

    <div class="row" style="margin: 0">
        <?php
        $has_result = false;

        while ($event = $events_history->fetch_assoc()) {
            $event_id = $event["Event_ID"];
            $prepared_event->execute();
            $status = $prepared_event->get_result();
            $status = $status->fetch_assoc();
            $status = $status["Event_Approval"];
            $has_result = true;
            $event_starttime = date("h:i A", strtotime($event["Event_StartDateTime"]));
            $event_endtime = date("h:i A", strtotime($event["Event_EndDateTime"]));
        ?>
        <div class="col <?php if (!isset($club_id)) { echo "xl3 l4";} ?> m6 s12">
            <div class="card sticky-action blue-grey darken-1 waves-effect waves-light">
                <div class="card-image activator">
                    <img src="resource/images/eventlogo/<?php echo $event['Event_Logo'] ?>">
                </div>
                <div class="card-content activator">
                    <span class="card-title grey-text text-lighten-4"><?php echo $event["Event_Name"] ?><i class="material-icons right">more_vert</i></span>
                </div>
                <div class="card-reveal">
                    <p class="grey-text text-darken-4"><?php if ($status == "Canceled") {echo "[Canceled]";} ?></p>
                    <span class="card-title grey-text text-darken-4"><?php echo $event["Event_Name"] ?><i class="material-icons right">close</i></span>
                    <p>Description: <?php echo $event["Event_Description"] ?></p>
                    <p>Time: <?php echo $event_starttime . " - " . $event_endtime; ?></p>
                    <p>Location: <?php echo $event["Event_Location"] ?></p>
                </div>
                <div class="card-action blue-grey darken-2">
                    <a onclick="openPage('event.php', null, '<?php echo $event['Event_ID'] ?>');">View Event</a>
                </div>
            </div>
        </div>
    <?php }
    if ($has_result == false) { ?>
        <div class="col s12" style="text-align: center; margin: 15px 0">No events</div>
    <?php } ?>
    </div>
<?php }
$link->close();
?>