<?php
session_start();
date_default_timezone_set("Asia/Kuala_Lumpur");
include("phpscript/link.php");

function isLoggedIn() {
    if (isset($_SESSION["user_id"]) && isset($_SESSION["first_name"]) && isset($_SESSION["last_name"]) && isset($_SESSION["email1"]) && isset($_SESSION["contact_number1"]) && isset($_SESSION["profile_picture"]) && isset($_SESSION["role"])) {
        return true;
    }
    return false;
}

function isAdmin() {
    if (isLoggedIn() && $_SESSION["role"] == 0) {
        return true;
    }
    return false;
}

function isAdvisor() {
    if (isLoggedIn() && $_SESSION["role"] == 1) {
        return true;
    }
    return false;
}

function isCommittee() {
    if (isLoggedIn() && $_SESSION["role"] == 2) {
        return true;
    }
    return false;
}

if (isset($_SESSION["user_id"])) {
    $user_id = strtoupper($_SESSION["user_id"]);
}

function isEventCommittee() {
    include("phpscript/link.php");
    $user_id = $_SESSION["user_id"];
    $event_committee_sql = "SELECT events.Event_ID FROM events INNER JOIN users_events ON events.Event_ID=users_events.Event_ID WHERE User_ID='$user_id' AND (UsersEvent_Role BETWEEN 2 AND 3) AND NOT (Event_Approval='Canceled' OR Event_Approval='Pending' OR Event_Approval='Updating') AND NOT Event_StartDateTime<=CURRENT_DATE()";
    $event_committee = $link->query($event_committee_sql);
    if ($event_committee->num_rows > 0) {
        return true;
    }
    return false;
}
$error = false;
?>