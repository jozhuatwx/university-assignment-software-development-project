<?php
@include_once("../server.php");

if (isLoggedIn()) {
    if (isset($_POST["club_id"])) {
        $club_id = $link->real_escape_string($_POST["club_id"]);
    } elseif (isset($_GET["club_id"])) {
        $club_id = $link->real_escape_string($_GET["club_id"]);
    }

    // Get Club Role
    $club_role_sql =    "SELECT UsersClub_Role, UsersClub_CommitteeRole, UsersClub_Approval
                        FROM users_clubs
                        WHERE User_ID='$user_id' AND Club_ID='$club_id'
                        AND (UsersClub_Approval='Approved' OR UsersClub_Approval='Terminate')";
    if ($club_role_detail = $link->query($club_role_sql)->fetch_assoc()) {
        $club_role = $club_role_detail["UsersClub_Role"];
        $usersclub_approval = $club_role_detail["UsersClub_Approval"];
        if ($club_role == 2) {
            $committee_role = $club_role_detail["UsersClub_CommitteeRole"];
        }
    } else {
        $club_role = 4;
    }

    // Get Club Details
    $club_sql = "SELECT Club_Name, Club_Day, Club_StartTime, Club_EndTime, Club_Location, Club_Logo
                FROM clubs
                WHERE Club_ID='$club_id'";
    $club = $link->query($club_sql)->fetch_assoc();

    $club_name = $club["Club_Name"];
    $club_day = $club["Club_Day"];
    $club_starttime = date("h:i A", strtotime($club["Club_StartTime"]));
    $club_endtime = date("h:i A", strtotime($club["Club_EndTime"]));
    $club_location = $club["Club_Location"];
    $club_logo = $club["Club_Logo"];
}
?>