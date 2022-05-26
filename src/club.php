<?php
include_once("server.php");
if (!isLoggedIn()) {
    header("Location: login.php");
}
include_once("phpscript/club.php");
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
    <?php if ($club_role <= 3 || isAdmin()) { ?>
    <nav style="margin-left: 32px">
        <h4 class="white-text" style="margin: 0 -2px"><?php echo $club["Club_Name"] ?></h4>
        <div class="nav-wrapper">
            <div class="col s12">
                <a class="breadcrumb"><?php echo $club["Club_Name"] ?></a>
                <a id="sub-breadcrumb" class="breadcrumb">Announcements</a>
            </div>
        </div>
    </nav>
    <?php } else { ?>
    <h4 class="white-text" style="margin: 0 30px"><?php echo $club["Club_Name"] ?></h4>
    <?php } ?>
</div>

<!-- Content -->
<div class="row" style="margin: 15px 20px">
    <!-- Subcontent Frame -->
    <div class="subcontent col xl8 s12">
    <?php if ($club_role <= 3 || isAdmin()) {
        include("club/announcement.php");
    } else {
        include("club/join.php");
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
                            <li class="tab"><a href="#clubdetails" class="active">Details</a></li>
                            <?php if ($club_role <= 3 || isAdmin()) { ?>
                            <li class="tab"><a href="#clublinks">Links</a></li>
                            <?php } ?>
                        </ul>
                    </div>

                    <!-- Card Tab Contents -->
                    <div class="card-content blue-grey lighten-5" style="padding: 1px 10px 0 10px;">
                        <div id="clubdetails">
                            <ul class="collection" style="<?php if ($club_role <= 2 && !isAdmin()) { echo "height: 425px"; } ?>">
                                <?php if ($club_role <= 2 && !isAdmin()) { ?>
                                <!-- Club Logo -->
                                <li class="collection-item avatar">
                                    <img id="image-preview" src="resource/images/clublogo/<?php echo $club_logo ?>?rnd=<?php echo mt_rand(0, 9999) ?>" alt="" class="circle">
                                    <div class="file-field input-field" style="margin-top: 0;">
                                        <span class="title">Club Logo</span>
                                        <input id="logo" type="file" onchange="updateLogo('<?php echo $club_id ?>', null)" class="validate" required>
                                        <p>Update club logo</p>
                                    </div>
                                </li>
                                <?php } ?>
                                <!-- Days -->
                                <li class="collection-item avatar">
                                    <i class="material-icons circle red">date_range</i>
                                    <?php if ($club_role <= 2 && !isAdmin()) { ?>
                                    <div class="input-field" onchange="updateClubDetails('days', '<?php echo $club_id ?>', this)">
                                        <select id="days" class="validate" multiple required>
                                            <?php $days = array("Mondays", "Tuesdays", "Wednesdays", "Thursdays", "Fridays", "Not Fixed");
                                            $selected_days = explode(", ", $club_day);

                                            foreach ($days as $day) { ?>
                                            <option <?php if (in_array($day, $selected_days)) { echo "selected"; } ?>><?php echo $day ?></option>
                                            <?php } ?>
                                        </select>
                                        <label class="active" for="days">Days</label>
                                    </div>
                                    <?php } else { ?>
                                    <span class="title">Days</span>
                                    <p><?php echo $club_day ?></p>
                                    <?php } ?>
                                </li>
                                <!-- Time -->
                                <li class="collection-item avatar">
                                    <i class="material-icons circle yellow darken-2">schedule</i>
                                    <?php if ($club_role <= 2 && !isAdmin()) { ?>
                                    <div class="row">
                                        <div class="input-field col m6 s12" onchange="updateClubDetails('starttime', '<?php echo $club_id ?>', this)">
                                            <input id="starttime" type="time" value="<?php echo date("H:i", strtotime($club_starttime)) ?>" class="timepicker validate" required>
                                            <label class="active" for="starttime">Start Time</label>
                                        </div>
                                        <div class="input-field col m6 s12" onchange="updateClubDetails('endtime', '<?php echo $club_id ?>', this)">
                                            <input id="endtime" type="time" value="<?php echo date("H:i", strtotime($club_endtime)) ?>" class="timepicker validate" required>
                                            <label class="active" for="endtime">End Time</label>
                                        </div>
                                    </div>
                                    <?php } else { ?>
                                    <span class="title">Time</span>
                                    <p><?php echo $club_starttime . " - " . $club_endtime; ?></p>
                                    <?php } ?>
                                </li>
                                <!-- Location -->
                                <li class="collection-item avatar">
                                    <i class="material-icons circle green">location_on</i>
                                    <?php if ($club_role <= 2 && !isAdmin()) { ?>
                                    <div class="input-field" onfocusout="updateClubDetails('location', '<?php echo $club_id ?>', this)">
                                        <input id="location" type="text" value="<?php echo $club_location ?>" class="validate" maxlength="40" required>
                                        <label class="active" for="location">Location</label>
                                    </div>
                                    <?php } else { ?>
                                    <span class="title">Location</span>
                                    <p><?php echo $club_location ?></p>
                                    <?php } ?>
                                </li>
                            </ul>
                        </div>

                        <?php if ($club_role <= 3 || isAdmin()) { ?>
                        <div id="clublinks">
                            <ul class="collection">
                                <!-- Announcements -->
                                <li class="collection-item avatar link" onclick="openSubpage('club/announcement.php', 'Announcements', '<?php echo $club_id ?>')">
                                    <i class="material-icons circle yellow darken-2">view_day</i>                                    
                                    <span class="title">Announcements</span>
                                    <?php if ($club_role <= 2 && !isAdmin()) { ?>
                                    <p>Manage announcements</p>
                                    <?php } else { ?>
                                    <p>View announcements</p>
                                    <?php } ?>
                                </li>
                                <?php if ($club_role <= 2) { ?>
                                <!-- Attendance -->
                                <li class="collection-item avatar link" onclick="openSubpage('club/attendance.php', 'Attendance', '<?php echo $club_id ?>')">
                                    <i class="material-icons circle blue">list</i>                                    
                                    <span class="title">Attendance</span>
                                    <?php if ($club_role == 2 && !isAdmin()) { ?>
                                    <p>View or take members' attendance</p>
                                    <?php } else { ?>
                                    <p>View members' attendance</p>
                                    <?php } ?>
                                </li>
                                <?php } ?>
                                <!-- Members -->
                                <li class="collection-item avatar link" onclick="openSubpage('club/member.php', 'Members', '<?php echo $club_id ?>')">
                                    <i class="material-icons circle green">group</i>
                                    <span class="title">Members</span>
                                    <?php if ($club_role <= 1 && !isAdmin()) { ?>
                                    <p>Manage club members</p>
                                    <?php } else { ?>
                                    <p>View club members</p>
                                    <?php }
                                    if ($club_role <= 2 && !isAdmin()) {
                                        // Retrieve Member Enrolment or Termination
                                        $prepared_members = $link->prepare("SELECT User_ID FROM users_clubs WHERE UsersClub_Approval=? AND UsersClub_Role>=2 AND Club_ID=?");
                                        $prepared_members->bind_param("ss", $approval, $club_id);

                                        if ($club_role == 2) {
                                            $approval = "Pending";
                                        } elseif ($club_role == 1) {
                                            $approval = "Terminate";
                                        }
                                    
                                        $prepared_members->execute();
                                        $member_requests = $prepared_members->get_result();
                                        
                                        if ($member_requests->num_rows > 0) { ?>
                                            <span id="notify-member" class="secondary-content new badge yellow darken-2" data-badge-caption="request(s)"><?php echo $member_requests->num_rows ?></span>
                                        <?php }
                                    } ?>
                                    
                                </li>
                                <!-- Committee -->
                                <li class="collection-item avatar link" onclick="openSubpage('club/committee.php', 'Committee', '<?php echo $club_id ?>' )">
                                    <i class="material-icons circle purple">group</i>
                                    <span class="title">Committee</span>
                                    <?php if ($club_role <= 1 && !isAdmin()) { ?>
                                    <p>Manage club committee members</p>
                                    <?php } else { ?>
                                    <p>View club committee members</p>
                                    <?php } ?>
                                </li>
                                <!-- Events -->
                                <li class="collection-item avatar link" onclick="openSubpage('club/event.php', 'Events', '<?php echo $club_id ?>' )">
                                    <i class="material-icons circle pink">event</i>
                                    <span class="title">Events</span>
                                    <?php if ($club_role <= 2 && !isAdmin()) { ?>
                                    <p>Manage club events</p>
                                    <?php } else { ?>
                                    <p>View club events</p>
                                    <?php }
                                    if ($club_role == 1 && !isAdmin()) {
                                        // Retrieve Event Request
                                        $events_sql = "SELECT Event_ID FROM events WHERE Club_ID='$club_id' AND (Event_Approval='Pending' OR Event_Approval='Updating' OR Event_Approval='Canceling')";
                                        $events = $link->query($events_sql);

                                        if ($events->num_rows > 0) { ?>
                                            <span id="notify-event" class="secondary-content new badge yellow darken-2" data-badge-caption="request(s)"><?php echo $events->num_rows ?></span>
                                        <?php }
                                    } ?>
                                </li>
                                <?php if (($club_role == 2 || $club_role == 3) && !isAdmin()) { ?>
                                <!-- Leave Club -->
                                <li class="collection-item avatar link" onclick="openSubpage('club/leave.php', 'Leave Club', '<?php echo $club_id ?>' )">
                                    <i class="material-icons circle red darken-2">exit_to_app</i>
                                    <span class="title">Leave Club</span>
                                    <p>Request to leave club</p>
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