<?php
include_once("server.php");
if (!isLoggedIn()) {
    header("Location: login.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>APU Co-curriculum</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Compiled and minified CSS -->
    <link type="text/css" rel="stylesheet" href="resource/materialize.min.css"  media="screen,projection"/>
    <link type="text/css" rel="stylesheet" href="resource/main.css" media="screen"/>
</head>
<body>
    <style>
    .sidenav .badge {
        position: fixed;
        right: 15px;
    }
    </style>
    <!-- Side Navigation -->
    <ul id="slide-out" class="sidenav sidenav-fixed">
        <!-- Profile -->
        <li>
            <div class="user-view">
                <div class="background">
                    <img src="resource/images/apu_aerial.jpg">
                </div>
                <div><img class="circle" src="resource/images/profilepicture/<?php echo $_SESSION["profile_picture"] ?>"></div>
                <div><span class="white-text name"><?php echo $_SESSION["first_name"] . " " . $_SESSION["last_name"]; ?></span></div>
                <div><span class="white-text email"><?php echo $_SESSION["email1"] ?></span></div>
            </div>
        </li>
        
        <!-- Home -->
        <li class=" active"><a class="waves-effect" onclick="openPage('home.php');"><i class="material-icons">home</i>Home</a></li>


        <li><div class="divider"></div></li>
        <?php if (isAdmin()) { ?>
        <li><a class="subheader">Management</a></li>
        <!-- Club Advisors -->
        <li><a class="waves-effect" onclick="openPage('advisor.php');"><i class="material-icons">group</i>Club Advisors</a></li>
        <!-- Reports & Statistics -->
        <li><a class="waves-effect" onclick="openPage('report.php');"><i class="material-icons">timeline</i>Reports & Statistics</a></li>        
        
        <?php } else { ?>

        <!-- My Clubs -->
        <li><a class="subheader">My Clubs</a></li>
        <?php // Retrieve Club Info
        $clubs_sql =    "SELECT clubs.Club_ID, clubs.Club_Name, clubs.Club_Logo, users_clubs.UsersClub_Role
                        FROM clubs
                        INNER JOIN users_clubs ON clubs.Club_ID=users_clubs.Club_ID
                        WHERE users_clubs.User_ID='$user_id' AND (users_clubs.UsersClub_Approval='Approved' OR users_clubs.UsersClub_Approval='Terminate')";
        $clubs = $link->query($clubs_sql);

        $prepared_announcements = $link->prepare("SELECT Announcement_ID FROM announcements WHERE Club_ID=? AND Announcement_DateTime>=(SELECT User_LastOnline FROM users WHERE User_ID=?)");
        $prepared_announcements->bind_param("ss", $club_id, $user_id);

        if ($clubs->num_rows > 0) {
            while ($club = $clubs->fetch_assoc()) {
                $club_id = $club["Club_ID"];
                $total_notifcation = 0;
                if ($club["UsersClub_Role"] == 1) {
                    $member = $link->query("SELECT User_ID FROM users_clubs WHERE Club_ID='$club_id' AND UsersClub_Approval='Terminate'");
                    $event = $link->query("SELECT Event_ID FROM events WHERE Club_ID='$club_id' AND (Event_Approval='Pending' OR Event_Approval='Updating' OR Event_Approval='Canceling')");
        
                    $total_notifcation += $member->num_rows + $event->num_rows;
                } elseif ($club["UsersClub_Role"] == 2) {
                    $member_sql = "SELECT User_ID FROM users_clubs WHERE Club_ID='$club_id' AND UsersClub_Approval='Pending'";
                    $member = $link->query($member_sql);
        
                    $total_notifcation = $member->num_rows;
                }
                $prepared_announcements->execute();
                $new_announcements = $prepared_announcements->get_result(); ?>
            <li>
                <a class="waves-effect" onclick="openPage('club.php', '<?php echo $club_id ?>');">
                    <img id="navicon-<?php echo $club_id ?>" src="resource/images/clublogo/<?php echo $club['Club_Logo'] ?>?rnd=<?php echo mt_rand(0, 9999) ?>" class="navclub-icons">
                    <?php echo $club["Club_Name"];
                     if ($total_notifcation > 0) { ?>
                    <span id="notify-<?php echo $club_id ?>" class="new badge red darken-2" data-badge-caption=""><?php echo $total_notifcation ?></span>
                    <?php } ?>
                </a>
            </li>
        <?php }
        } else { ?>
        <div style="margin: 10px 0 40px 95px">Join a club now!</div>
        <?php } ?>

        <div id="my-upcoming-events">
        <?php include_once("phpscript/sidenavevent.php"); ?>
        </div>

        <li><div class="divider"></div></li>
        <li><a class="subheader">More</a></li>
        <?php } ?>
        
        <!-- All Club Events -->
        <li><a class="waves-effect" onclick="openPage('allevents.php');"><i class="material-icons">event</i>All Club Events</a></li>        
        <!-- All Clubs & Societies -->
        <li><a class="waves-effect" onclick="openPage('allclubs.php');"><i class="material-icons">supervised_user_circle</i>All Clubs & Societies</a></li>
        
        <?php if (isAdmin() || isCommittee() || isEventCommittee()) { ?>
        <!-- University Facilities -->
        <li>
            <a class="waves-effect" onclick="openPage('facility.php');"><i class="material-icons">account_balance</i>University Facilities
            <?php
            if (isAdmin()) {
                $room_request = $link->query("SELECT RoomsRequest_ID FROM rooms_requests WHERE RoomsRequest_Approval='Pending' AND RoomsRequest_StartDateTime >= NOW()");
                $transport_requests = $link->query("SELECT TransportationsRequest_ID FROM transportations_requests WHERE TransportationsRequest_Approval='Pending' AND TransportationsRequest_DepartureDateTime >= NOW()");

                $total_notifcation = $room_request->num_rows + $transport_requests->num_rows;

                if ($total_notifcation > 0) { ?>
                <span id="notify-facility" class="new badge red darken-2" data-badge-caption=""><?php echo $total_notifcation ?></span>
            <?php }
            } ?>
            </a>
        </li>
        <?php }
        
        if (isAdmin()) { ?>
        <li><div class="divider"></div></li>
        <li><a class="subheader">More</a></li>
        <?php } ?>
        <!-- Logout -->
        <li><a href="phpscript/logout.php" class="waves-effect"><i class="material-icons">exit_to_app</i>Logout</a></li>

        <li><div class="divider"></div></li>
        <li><a class="subheader">v1.0</a></li>
    </ul>
    <div data-target="slide-out" class="sidenav-trigger white blue-grey-text text-lighten-1"><span>MENU</span><i class="material-icons small">menu</i></div>
    
    <!-- Modal Structure -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <h4 id="modal-title"></h4>
            <p id="modal-content"></p>
        </div>
        <div class="modal-footer">
            <a id="modal-button-confirm" class="modal-close waves-effect waves-green btn-flat">Confirm</a>
            <a class="modal-close waves-effect waves-green btn-flat" onclick="modals[0].close()">Cancel</a>
        </div>
    </div>
    
    <!-- Content -->
    <div class="maincontent">
    <?php include("home.php") ?>
    </div>
    
    <!-- Compiled and minified JavaScript -->
    <script type="text/javascript" src="resource/materialize.min.js"></script>
    <script type="text/javascript" src="resource/main.js"></script>
    <script type="text/javascript">
        setInterval(function() {
            var xhttp = new XMLHttpRequest();
            xhttp.open("POST", "phpscript/lastonline.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send();
        }, 5000);
    </script>
</body>
</html>