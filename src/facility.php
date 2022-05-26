<?php
include_once("server.php");
if (!isLoggedIn()) {
    header("Location: login.php");
}
if (!isAdmin() && !isCommittee() && !isEventCommittee()) {
    header("Location: nopermission.php");
}

function requestCategory($string) {
    switch ($string) {
        case "T":
            echo "Transportation";
            break;
        
        case "R":
            echo "Room / Auditorium";
            break;
    }
}

function approvalIcon($string) {
    switch ($string) {
        case "Approved":
            echo "done";
            break;

        case "Pending":
            echo "schedule";
            break;
        
        case "Rejected":
            echo "close";
            break;
    }
}

$user_id = $_SESSION["user_id"];
$requests_array = array();

if (isCommittee() || isEventCommittee()) {
    if (isCommittee()) {
        // Retrieve Club Requests
        $prepared_room_requests =   $link->prepare("SELECT rooms_requests.RoomsRequest_ID, rooms_requests.RoomsRequest_Approval, rooms_requests.RoomsRequest_StartDateTime, clubs.Club_Name, clubs.Club_Logo, rooms.Room_Name
                                    FROM rooms_requests
                                    INNER JOIN clubs ON rooms_requests.Club_ID=clubs.Club_ID
                                    INNER JOIN rooms ON rooms_requests.Room_ID=rooms.Room_ID
                                    WHERE clubs.Club_ID=?");
        $prepared_room_requests->bind_param("s", $club_id);

        $prepared_transport_requests =  $link->prepare("SELECT transportations_requests.TransportationsRequest_ID, transportations_requests.TransportationsRequest_Approval, transportations_requests.TransportationsRequest_DepartureDateTime, clubs.Club_Name, clubs.Club_Logo, transportations.Transportation_Type, transportations.Transportation_Seats
                                        FROM transportations_requests
                                        INNER JOIN clubs ON transportations_requests.Club_ID=clubs.Club_ID
                                        INNER JOIN transportations ON transportations_requests.Transportation_ID=transportations.Transportation_ID
                                        WHERE clubs.Club_ID=?");
        $prepared_transport_requests->bind_param("s", $club_id);

        // Retrieve My Clubs
        $clubs_sql =    "SELECT clubs.Club_ID, clubs.Club_Name
                        FROM clubs
                        INNER JOIN users_clubs ON clubs.Club_ID=users_clubs.Club_ID
                        WHERE users_clubs.UsersClub_Role=2 AND users_clubs.User_ID='$user_id'";
        $clubs = $link->query($clubs_sql);
        $clubs_array = array();

        while ($club = $clubs->fetch_assoc()) {
            $clubs_array[] = $club;

            $club_id = $club["Club_ID"];

            $prepared_room_requests->execute();
            $room_requests = $prepared_room_requests->get_result();
            while ($room_request = $room_requests->fetch_array()) {
                $requests_array[] = $room_request;
            }

            $prepared_transport_requests->execute();
            $transport_requests = $prepared_transport_requests->get_result();
            while ($transport_request = $transport_requests->fetch_array()) {
                $requests_array[] = $transport_request;
            }
        }
    }
    if (isEventCommittee()) {
        // Retrieve Event Requests
        $prepared_room_requests =   $link->prepare("SELECT rooms_requests.RoomsRequest_ID, rooms_requests.RoomsRequest_Approval, rooms_requests.RoomsRequest_StartDateTime, events.Event_Name, events.Event_Logo, rooms.Room_Name
                                                    FROM rooms_requests
                                                    INNER JOIN events ON rooms_requests.Event_ID=events.Event_ID
                                                    INNER JOIN rooms ON rooms_requests.Room_ID=rooms.Room_ID
                                                    WHERE events.Event_ID=?");
        $prepared_room_requests->bind_param("s", $event_id);

        $prepared_transport_requests =  $link->prepare("SELECT transportations_requests.TransportationsRequest_ID, transportations_requests.TransportationsRequest_Approval, transportations_requests.TransportationsRequest_DepartureDateTime, events.Event_Name, events.Event_Logo, transportations.Transportation_Type, transportations.Transportation_Seats
                                                        FROM transportations_requests
                                                        INNER JOIN events ON transportations_requests.Event_ID=events.Event_ID
                                                        INNER JOIN transportations ON transportations_requests.Transportation_ID=transportations.Transportation_ID
                                                        WHERE events.Event_ID=?");
        $prepared_transport_requests->bind_param("s", $event_id);

        // Retrieve My Events
        $events_sql =   "SELECT events.Event_ID, events.Event_Name
                        FROM events
                        INNER JOIN users_events ON events.Event_ID=users_events.Event_ID
                        WHERE (users_events.UsersEvent_Role BETWEEN 2 AND 3) AND users_events.User_ID='$user_id'
                        AND NOT (Event_Approval='Canceled' OR Event_Approval='Pending' OR Event_Approval='Updating') AND NOT Event_StartDateTime<=CURRENT_DATE()";
        $events = $link->query($events_sql);
        $events_array = array();

        while ($event = $events->fetch_assoc()) {
            $events_array[] = $event;

            $event_id = $event["Event_ID"];

            $prepared_room_requests->execute();
            $room_requests = $prepared_room_requests->get_result();
            while ($room_request = $room_requests->fetch_array()) {
                $requests_array[] = $room_request;
            }

            $prepared_transport_requests->execute();
            $transport_requests = $prepared_transport_requests->get_result();
            while ($transport_request = $transport_requests->fetch_array()) {
                $requests_array[] = $transport_request;
            }
        }
    }
    
    // Retrieve Transportation
    $transports_sql = "SELECT * FROM transportations";
    $transports = $link->query($transports_sql);
} elseif (isAdmin()) {
    // Retrieve Club Requests
    $prepared_room_requests =   $link->prepare("SELECT rooms_requests.RoomsRequest_ID, rooms_requests.RoomsRequest_Approval, rooms_requests.RoomsRequest_StartDateTime, clubs.Club_Name, clubs.Club_Logo, rooms.Room_Name
                                FROM rooms_requests
                                INNER JOIN clubs ON rooms_requests.Club_ID=clubs.Club_ID
                                INNER JOIN rooms ON rooms_requests.Room_ID=rooms.Room_ID");

    $prepared_transport_requests =  $link->prepare("SELECT transportations_requests.TransportationsRequest_ID, transportations_requests.TransportationsRequest_Approval, transportations_requests.TransportationsRequest_DepartureDateTime, clubs.Club_Name, clubs.Club_Logo, transportations.Transportation_Type
                                    FROM transportations_requests
                                    INNER JOIN clubs ON transportations_requests.Club_ID=clubs.Club_ID
                                    INNER JOIN transportations ON transportations_requests.Transportation_ID=transportations.Transportation_ID");
    $prepared_room_requests->execute();
    $room_requests = $prepared_room_requests->get_result();
    while ($room_request = $room_requests->fetch_array()) {
        $requests_array[] = $room_request;
    }

    $prepared_transport_requests->execute();
    $transport_requests = $prepared_transport_requests->get_result();
    while ($transport_request = $transport_requests->fetch_array()) {
        $requests_array[] = $transport_request;
    }

    // Retrieve Event Requests
    $prepared_room_requests =   $link->prepare("SELECT rooms_requests.RoomsRequest_ID, rooms_requests.RoomsRequest_Approval, rooms_requests.RoomsRequest_StartDateTime, events.Event_Name, events.Event_Logo, rooms.Room_Name
                                FROM rooms_requests
                                INNER JOIN events ON rooms_requests.Event_ID=events.Event_ID
                                INNER JOIN rooms ON rooms_requests.Room_ID=rooms.Room_ID");

    $prepared_transport_requests =  $link->prepare("SELECT transportations_requests.TransportationsRequest_ID, transportations_requests.TransportationsRequest_Approval, transportations_requests.TransportationsRequest_DepartureDateTime, events.Event_Name, events.Event_Logo, transportations.Transportation_Type
                                    FROM transportations_requests
                                    INNER JOIN events ON transportations_requests.Event_ID=events.Event_ID
                                    INNER JOIN transportations ON transportations_requests.Transportation_ID=transportations.Transportation_ID");
    $prepared_room_requests->execute();
    $room_requests = $prepared_room_requests->get_result();
    while ($room_request = $room_requests->fetch_array()) {
        $requests_array[] = $room_request;
    }

    $prepared_transport_requests->execute();
    $transport_requests = $prepared_transport_requests->get_result();
    while ($transport_request = $transport_requests->fetch_array()) {
        $requests_array[] = $transport_request;
    }
}

$prepared_room_requests->close();
$prepared_transport_requests->close();

// Sort Request Array Chronologically and into Upcoming and History
$requests_upcoming_array = array();
$requests_history_array = array();

$upcoming_starttime = array();
$history_starttime = array();

foreach ($requests_array as $request) {
    if (strtotime($request[2]) > strtotime("now")) {
        $requests_upcoming_array[] = $request;
        $upcoming_starttime[] = $request[2];
    } else {
        $requests_history_array[] = $request;
        $history_starttime[] = $request[2];
    }
}

array_multisort($upcoming_starttime, SORT_ASC, $requests_upcoming_array);
array_multisort($history_starttime, SORT_DESC, $requests_history_array); ?>

<div class="row" style="margin: 15px 20px">
    <div class="subcontent col xl8 s12">
    <?php if (isCommittee() || isEventCommittee()) {
        include_once("facility/form.php");
    } elseif (isAdmin()) {
        include_once("facility/approval.php");
    } ?>
    </div>

    <!-- Request Status -->
    <div class="col xl4 s12">
        <span class="section-title">Request Status</span>
        <div class="row">
            <div class="col s12">
                <div class="card blue-grey darken-1">
                    <div class="card-content white-text">
                    <?php if (isAdmin()) { ?>
                    <a class="waves-effect waves-light white-text btn-flat left" onclick="bookingReport()"><i class="material-icons left">print</i>Print Approved List (This Month)</a>
                    <?php } ?>
                    </div>
                    <div class="card-tabs">
                        <ul class="tabs tabs-fixed-width tabs-transparent">
                            <li class="tab"><a class="active" href="#statusupcoming">Upcoming</a></li>
                            <li class="tab"><a href="#statushistory">History</a></li>
                        </ul>
                    </div>
                    <div class="card-content blue-grey lighten-5" style="padding: 1px 10px 0 10px;">
                        <div id="statusupcoming">
                            <ul class="collection">
                                <?php foreach ($requests_upcoming_array as $request_upcoming) { ?>
                                    <li class="collection-item avatar link" onclick="openSubpage('facility/details.php', null, null, null, '<?php echo $request_upcoming[0] ?>')">
                                        <img src="resource/images/<?php if (strlen($request_upcoming[4]) <= 11) {echo "clublogo/" . $request_upcoming[4];} else {echo "eventlogo/" . $request_upcoming[4];} ?>" alt="" class="circle">
                                        <span class="title"><?php echo $request_upcoming[3] ?></span>
                                        <p><?php requestCategory($request_upcoming[0][0]) ?></p>
                                        <a class="secondary-content"><i class="material-icons small">
                                            <?php approvalIcon($request_upcoming[1]) ?>
                                        </i></a>
                                    </li>    
                                <?php } ?>
                            </ul>
                        </div>
                        
                        <div id="statushistory">
                            <ul class="collection">
                                <?php foreach ($requests_history_array as $request_history) { ?>
                                    <li class="collection-item avatar link" onclick="openSubpage('facility/details.php', null, null, null, '<?php echo $request_history[0] ?>')">
                                        <img src="resource/images/<?php if (strlen($request_history[4]) <= 11) {echo "clublogo/" . $request_history[4];} else {echo "eventlogo/" . $request_history[4];} ?>" alt="" class="circle">
                                        <span class="title"><?php echo $request_history[3] ?></span>
                                        <p><?php requestCategory($request_history[0][0]) ?></p>
                                        <a class="secondary-content"><i class="material-icons small">
                                            <?php approvalIcon($request_history[1])?>
                                        </i></a>
                                    </li>    
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>