<?php
include_once("../server.php");

if (isLoggedIn()) {
    if (isset($_POST["action"])) {
        $action = $link->real_escape_string($_POST["action"]);
    } else {
        $error = true;
    }
    if (isset($_POST["club_id"])) {
        $club_id = $link->real_escape_string($_POST["club_id"]);
    }
    if (isset($_POST["event_id"])) {
        $event_id = $link->real_escape_string($_POST["event_id"]);
    }

    $total = 0;
    if (isset($club_id) && !$error) {
        $club_sql = "SELECT UsersClub_Role FROM users_clubs WHERE User_ID='$user_id' AND Club_ID='$club_id'";
        $club = $link->query($club_sql)->fetch_assoc();
        $club_role = $club["UsersClub_Role"];

        $member_advisor_sql = "SELECT User_ID FROM users_clubs WHERE Club_ID='$club_id' AND UsersClub_Approval='Terminate'";
        $member_committee_sql = "SELECT User_ID FROM users_clubs WHERE Club_ID='$club_id' AND UsersClub_Approval='Pending'";
        $event_sql = "SELECT Event_ID FROM events WHERE Club_ID='$club_id' AND (Event_Approval='Pending' OR Event_Approval='Updating' OR Event_Approval='Canceling')";

        if ($action == "main" && $club_role == 1) {
            $member = $link->query($member_advisor_sql);
            $event = $link->query($event_sql);
            $total = $member->num_rows + $event->num_rows;
        } elseif (($action == "main" || $action == "member") && $club_role == 2) {
            $member = $link->query($member_committee_sql);
            $total = $member->num_rows;
        } elseif ($action == "member" && $club_role == 1) {
            $member = $link->query($member_advisor_sql);
            $total = $member->num_rows;
        } elseif ($action == "event" && $club_role == 1) {
            $event = $link->query($event_sql);
            $total = $event->num_rows;
        }
    } elseif (isset($event_id) && !$error) {
        $event_sql = "SELECT UsersEvent_Role FROM users_events WHERE User_ID='$user_id' AND Event_ID='$event_id'";
        $event = $link->query($event_sql)->fetch_assoc();
        $event_role = $event["UsersEvent_Role"];

        $member_sql = "SELECT User_ID FROM users_events WHERE Event_ID='$event_id' AND UsersEvent_Role=4 AND (UsersEvent_Approval='Pending' OR UsersEvent_Approval='Terminate')";
        if (($action == "main" || $action == "member") && ($event_role == 2 || $event_role == 3)) {
            $member = $link->query($member_sql);
            $total = $member->num_rows;
        }
    } elseif (isAdmin() && $action == "facility" && !$error) {
        $room_request = $link->query("SELECT RoomsRequest_ID FROM rooms_requests WHERE RoomsRequest_Approval='Pending' AND RoomsRequest_StartDateTime >= NOW()");
        $transport_requests = $link->query("SELECT TransportationsRequest_ID FROM transportations_requests WHERE TransportationsRequest_Approval='Pending' AND TransportationsRequest_DepartureDateTime >= NOW()");

        $total = $room_request->num_rows + $transport_requests->num_rows;
    }
    echo $total;
?>

<?php } ?>