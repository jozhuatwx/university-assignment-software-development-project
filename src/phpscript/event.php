<?php
@include_once("../server.php");

if (isLoggedIn()) {
    if (isset($_POST["event_id"])) {
        $event_id = $link->real_escape_string($_POST["event_id"]);
    } elseif (isset($_GET["event_id"])) {
        $event_id = $link->real_escape_string($_GET["event_id"]);
    }

    // Get Event Role
    $event_role_sql =   "SELECT UsersEvent_Role, UsersEvent_Approval
                        FROM users_events
                        WHERE User_ID='$user_id' AND Event_ID='$event_id'
                        AND (UsersEvent_Approval='Approved' OR UsersEvent_Approval='Terminate')";
    if ($event_role_detail = $link->query($event_role_sql)->fetch_assoc()) {
        $usersevent_approval = $event_role_detail["UsersEvent_Approval"];
        $event_role = $event_role_detail["UsersEvent_Role"];
    } else {
        $event_role = 6;
    }

    // Get Event Details
    $event_sql =    "SELECT *
                    FROM events
                    WHERE Event_ID='$event_id'";
    $event = $link->query($event_sql)->fetch_assoc();

    $event_name = $event["Event_Name"];
    $event_description = $event["Event_Description"];
    $event_startday = date("Y-m-d", strtotime($event["Event_StartDateTime"]));
    $event_endday = date("Y-m-d", strtotime($event["Event_EndDateTime"]));
    $event_starttime = date("h:i A", strtotime($event["Event_StartDateTime"]));
    $event_endtime = date("h:i A", strtotime($event["Event_EndDateTime"]));
    $event_location = $event["Event_Location"];
    $event_logo = $event["Event_Logo"];
    $event_approval = $event["Event_Approval"];
    $main_clubid = $event["Club_ID"];
}
?>