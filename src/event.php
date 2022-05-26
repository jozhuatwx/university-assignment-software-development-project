<?php
include_once("server.php");
if (!isLoggedIn()) {
    header("Location: login.php");
}
include_once("phpscript/event.php");
?>
<style>
nav {
    background-color: transparent;
    box-shadow: none;
    line-height: 35px;
}

.collection-item .input-field {
    margin-bottom: 0;
}

.collection-item input[type=text],
.collection-item input[type=time] {
    height: 2rem;
    margin: 0;
}

.collection-item .input-field>label {
    top: -10px;
}

.collection-item .input-field>label.active,
.input-field>input[type=time]:not(.browser-default)+label {
    transform: translateY(-6px) scale(0.8);
}
</style>

<!-- Header -->
<div class="parallax-container" style="height: 176px; background-image: url('resource/images/apu_aerial.jpg');">
    <div style="height: 85px"></div>
    <?php if ($event_role <= 4 || isAdmin()) { ?>
    <nav style="margin-left: 32px">
        <h4 class="white-text" style="margin: 0 -2px"><?php echo $event["Event_Name"] ?></h4>
        <div class="nav-wrapper">
            <div class="col s12">
                <a class="breadcrumb"><?php echo $event["Event_Name"] ?></a>
                <a id="sub-breadcrumb" class="breadcrumb">Announcements</a>
            </div>
        </div>
    </nav>
    <?php } else { ?>
    <h4 class="white-text" style="margin: 0 30px"><?php echo $event["Event_Name"] ?></h4>
    <?php } ?>
</div>

<!-- Content -->
<div class="row" style="margin: 15px 20px">
    <!-- Subcontent Frame -->
    <div class="subcontent col xl8 s12">
    <?php if ($event_role <= 4 || isAdmin()) {
        include("event/announcement.php");
    } else {
        include("event/volunteer.php");
    } ?>
    </div>

    <!-- Sidebar Frame -->
    <div class="col xl4 s12">
        <div class="row">
            <div class="col s12">
                <!-- Sidebar -->
                <div class="card blue-grey darken-1">
                    <!-- Card Content -->
                    <div class="card-content white-text">
                    </div>

                    <!-- Card Tabs -->
                    <div class="card-tabs">
                        <ul class="tabs tabs-fixed-width tabs-transparent">
                            <li class="tab"><a href="#eventdetails" class="active">Details</a></li>
                            <?php if ($event_role <= 4 || isAdmin()) { ?>
                            <li class="tab"><a href="#eventlinks">Links</a></li>
                            <?php } ?>
                        </ul>
                    </div>

                    <!-- Card Tab Contents -->
                    <div class="card-content blue-grey lighten-5" style="padding: 1px 10px 0 10px;">
                        <div id="eventdetails">
                            <ul class="collection">
                                <?php if (($event_role == 2 || $event_role == 3) && !isAdmin() && strtotime($event_endday) >= strtotime("now") && $event_approval != "Canceled") { ?>
                                <!-- Event Logo -->
                                <li class="collection-item avatar">
                                    <img id="image-preview" src="resource/images/eventlogo/<?php echo $event_logo ?>?rnd=<?php echo mt_rand(0, 9999) ?>" alt="" class="circle">
                                    <div class="file-field input-field" style="margin-top: 0;">
                                        <span class="title">Event Logo</span>
                                        <input id="logo" type="file" onchange="updateLogo(null, '<?php echo $event_id ?>')" class="validate" required>
                                        <p>Update event logo</p>
                                    </div>
                                </li>
                                <?php } ?>
                                <!-- Date -->
                                <li class="collection-item avatar">
                                    <i class="material-icons circle red">date_range</i>
                                    <span class="title">Day</span>
                                    <p><?php if ($event_startday == $event_endday) {echo date("jS F Y (D)", strtotime($event_startday));} else {echo date("d/m/Y (D)", strtotime($event_startday)) . "-" . date("d/m/Y (D)", strtotime($event_endday));} ?></p>
                                </li>
                                <!-- Time -->
                                <li class="collection-item avatar">
                                    <i class="material-icons circle yellow darken-2">schedule</i>
                                    <span class="title">Time</span>
                                    <p><?php echo $event_starttime . " - " . $event_endtime; ?></p>
                                </li>
                                <!-- Location -->
                                <li class="collection-item avatar">
                                    <i class="material-icons circle green">location_on</i>
                                    <span class="title">Location</span>
                                    <p><?php echo $event_location ?></p>
                                </li>
                                <?php
                                if ($event_role == 2 || $event_role == 3) {
                                $updating_sql = "SELECT Event_Approval FROM events WHERE Old_ID='$event_id'";
                                $updating = $link->query($updating_sql)->fetch_assoc();

                                if ($updating["Event_Approval"] != "Updating" && $event_approval != "Canceled" && strtotime($event_endday) >= strtotime("now")) { ?>
                                <li id="update-event" class="collection-item avatar link" onclick="openSubpage('event/updateevent.php', 'Update Event', null, '<?php echo $event_id ?>')">
                                    <i class="material-icons circle light-blue">update</i>
                                    <span class="title">Update Details</span>
                                    <p>Request to update event details</p>
                                </li>
                                <?php } else { ?>
                                <li class="collection-item avatar grey-text text-darken-1 link" onclick="M.toast({html: '<?php if ($updating['Event_Approval'] == 'Updating') {echo 'The request sent is pending approval';} elseif ($event_approval == 'Canceled') {echo 'This event has been canceled';} elseif (strtotime($event_endday) >= strtotime('now')) {echo 'This event has ended';} ?>'})">
                                    <i class="material-icons circle grey darken-1">update</i>
                                    <span class="title">Update Details</span>
                                    <p><?php if ($updating['Event_Approval'] == 'Updating') {echo 'The request sent is pending approval';} elseif ($event_approval == 'Canceled') {echo 'This event has been canceled';} elseif (strtotime($event_endday) >= strtotime('now')) {echo 'This event has ended';} ?></p>
                                </li>
                                <?php }
                            } elseif ($event_role == 1) {
                                if ($event_approval != "Canceled" && strtotime($event_endday) >= strtotime("now")) { ?>
                                    <li class="collection-item avatar grey-text text-darken-1 link" onclick="M.toast({html: 'Only the committee can request update'})">
                                        <i class="material-icons circle grey">update</i>
                                        <span class="title">Update Details</span>
                                        <p>Only the committee can request update</p>
                                    </li>
                                <?php } else { ?>
                                    <li class="collection-item avatar grey-text text-darken-1 link" onclick="M.toast({html: '<?php if ($event_approval == 'Canceled') {echo 'This event has been canceled';} elseif (strtotime($event_endday) >= strtotime('now')) {echo 'This event has ended';} ?>'})">
                                        <i class="material-icons circle grey darken-1">update</i>
                                        <span class="title">Update Details</span>
                                        <p><?php if ($event_approval == 'Canceled') {echo 'This event has been canceled';} elseif (strtotime($event_endday) >= strtotime('now')) {echo 'This event has ended';} ?></p>
                                    </li>
                                <?php } ?>
                            <?php } ?>
                            </ul>
                        </div>

                        <?php if ($event_role <= 4 || isAdmin()) { ?>
                        <div id="eventlinks">
                            <ul class="collection">
                                <!-- Announcements -->
                                <li class="collection-item avatar link" onclick="openSubpage('event/announcement.php', 'Announcements', null, '<?php echo $event_id ?>')">
                                    <i class="material-icons circle yellow darken-2">view_day</i>                                    
                                    <span class="title">Announcements</span>
                                    <?php if ($event_role <= 3 && !isAdmin()) { ?>
                                    <p>Manage announcements</p>
                                    <?php } else { ?>
                                    <p>View announcements</p>
                                    <?php } ?>
                                </li>
                                <?php if ($event_role <= 3) { ?>
                                <!-- Attendance -->
                                <li class="collection-item avatar link" onclick="openSubpage('event/attendance.php', 'Attendance', null, '<?php echo $event_id ?>')">
                                    <i class="material-icons circle blue">list</i>                                    
                                    <span class="title">Attendance</span>
                                    <?php if (($event_role == 2 || $event_role == 3) && !isAdmin() && strtotime($event_endday) >= strtotime("now") && $event_approval != "Canceled") { ?>
                                    <p>View or take members' attendance</p>
                                    <?php } else { ?>
                                    <p>View members' attendance</p>
                                    <?php } ?>
                                </li>
                                <?php } ?>
                                <!-- Members -->
                                <li class="collection-item avatar link" onclick="openSubpage('event/member.php', 'Members', null, '<?php echo $event_id ?>')">
                                    <i class="material-icons circle green">group</i>
                                    <span class="title">Members</span>
                                    <?php if (($event_role == 2 || $event_role == 3) && !isAdmin()) { ?>
                                    <p>Manage event members</p>
                                    <?php } else { ?>
                                    <p>View event members</p>
                                    <?php }
                                    if (($event_role == 2 || $event_role == 3) && !isAdmin()) {
                                        // Retrieve Member Enrolment or Termination
                                        $member_requests_sql =  "SELECT users.User_ID, users.User_FirstName, users.User_LastName, users.User_ContactNumber1, users.User_EmailAddress1, users_events.UsersEvent_Approval
                                                                FROM users INNER JOIN users_events ON users.User_ID=users_events.User_ID
                                                                WHERE (users_events.UsersEvent_Approval='Pending' OR users_events.UsersEvent_Approval='Terminate')
                                                                AND (users_events.UsersEvent_Role BETWEEN 2 AND 4) AND users_events.Event_ID='$event_id'
                                                                ORDER BY users.User_FirstName ASC";
                                        $member_requests = $link->query($member_requests_sql);
                                        
                                        if ($member_requests->num_rows > 0) { ?>
                                            <span id="notify-member" class="secondary-content new badge yellow darken-2" data-badge-caption="request(s)"><?php echo $member_requests->num_rows ?></span>
                                        <?php }
                                    } ?>
                                    
                                </li>
                                <!-- Committee -->
                                <li class="collection-item avatar link" onclick="openSubpage('event/committee.php', 'Committee', null, '<?php echo $event_id ?>' )">
                                    <i class="material-icons circle purple">group</i>
                                    <span class="title">Committee</span>
                                    <?php if ($event_role == 2 && !isAdmin() && strtotime($event_endday) >= strtotime("now") && $event_approval != "Canceled") { ?>
                                    <p>Manage event committee members</p>
                                    <?php } else { ?>
                                    <p>View event committee members</p>
                                    <?php } ?>
                                </li>
                                <?php if (!isAdmin() && ($event_role == 3 || $event_role == 4)) { ?>
                                <!-- Leave Event -->
                                <li class="collection-item avatar link" onclick="openSubpage('event/leave.php', 'Leave Event', null, '<?php echo $event_id ?>' )">
                                    <i class="material-icons circle pink">exit_to_app</i>
                                    <span class="title">Leave Event</span>
                                    <p>Request to leave event</p>
                                </li>
                                <?php } elseif (!isAdmin() && $event_role == 2) { ?>
                                <li class="collection-item avatar grey-text text-darken-1 link" onclick="M.toast({html: 'The organiser cannot leave the event'})">
                                    <i class="material-icons circle grey darken-1">exit_to_app</i>
                                    <span class="title">Leave Event</span>
                                    <p>The organiser cannot leave the event</p>
                                </li>
                                <?php } ?>
                                <!-- Cancel Event -->
                                <?php if (!isAdmin() && $event_role == 2) {
                                    $canceling_sql = "SELECT Event_Approval FROM events WHERE Event_ID='$event_id'";
                                    $canceling = $link->query($canceling_sql)->fetch_assoc();
                                    if ($canceling["Event_Approval"] != "Canceling" && $event_approval != "Canceled") { ?>
                                <li id="cancel-event" class="collection-item avatar link" onclick="eventRequestConfirm('CancelEvent', '<?php echo $event_id ?>', null)">
                                    <i class="material-icons circle red darken-2">close</i>
                                    <span class="title">Cancel Event</span>
                                    <p>Request to cancel event</p>
                                </li>
                                <?php } else { ?>
                                <li class="collection-item avatar grey-text text-darken-1 link" onclick="M.toast({html: '<?php if ($canceling['Event_Approval'] == 'Canceling') {echo 'The request sent is pending approval';} elseif ($event_approval == 'Canceled') {echo 'This event has been canceled';} ?>'})">
                                    <i class="material-icons circle grey darken-1">close</i>
                                    <span class="title">Cancel Event</span>
                                    <p><?php if ($canceling["Event_Approval"] == "Canceling") {echo "The request sent is pending approval";} elseif ($event_approval == "Canceled") {echo "This event has been canceled";} ?></p>
                                </li>
                            <?php }
                            } elseif (!isAdmin() && $event_role == 3) { ?>
                                <li class="collection-item avatar grey-text text-darken-1 link" onclick="M.toast({html: 'Only the organiser can cancel'})">
                                    <i class="material-icons circle grey">close</i>
                                    <span class="title">Cancel Event</span>
                                    <p>Only the organiser can cancel</p>
                                </li>
                            <?php } ?>
                            </ul>
                        </div>
                        <?php }
                        $link->close();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>