<?php
include_once("../server.php");

if (isLoggedIn()) {
    // Validation
    if (isset($_POST["club_id"])) {
        $club_id = $link->real_escape_string($_POST["club_id"]);

        $club_role_sql =    "SELECT UsersClub_Role
                            FROM users_clubs
                            WHERE User_ID='$user_id'
                            AND Club_ID='$club_id'";

        $club_role = $link->query($club_role_sql)->fetch_assoc();
        $club_role = $club_role["UsersClub_Role"];
    } else {
        echo "Failed to get Club ID\n";
        $error = true;
    }

    if ($club_role == 1 && !isAdmin()) {
        if (isset($_POST["committee_role"])) {
            $committee_role = $link->real_escape_string($_POST["committee_role"]);
        } else {
            echo "Failed to get committee's role\n";
            $error = true;
        }
        if (isset($_POST["user_id"])) {
            $committee_user_id = $link->real_escape_string($_POST["user_id"]);

            // Check if promoted member has other role
            $committee_clubrole_sql =   "SELECT UsersClub_Role
                                        FROM users_clubs
                                        WHERE User_ID='$committee_user_id'
                                        AND Club_ID='$club_id'";
            $committee_clubrole = $link->query($committee_clubrole_sql)->fetch_assoc();
            $committee_clubrole = $committee_clubrole["UsersClub_Role"];
            
            if ($committee_clubrole == 2) {
                echo "Selected member already has an existing committee role";
                $error = true;
            }
        } else {
            echo "Failed to get committee's User ID\n";
            $error = true;
        }

        if (!$error) {
            // Demote user to regular member
            $demote_member_sql =    "UPDATE users_clubs SET UsersClub_Role=3, UsersClub_CommitteeRole=NULL
                                    WHERE Club_ID='$club_id' AND UsersClub_CommitteeRole='$committee_role'";
            if ($link->query($demote_member_sql)) {
                // Promote user to committee member
                $promote_member_sql =   "UPDATE users_clubs SET UsersClub_Role=2, UsersClub_CommitteeRole='$committee_role'
                                        WHERE User_ID='$committee_user_id' AND Club_ID='$club_id'";
                if ($link->query($promote_member_sql)) {
                    echo "Promoted " . $committee_user_id;
                } else {
                    echo "Failed to promote " . $committee_user_id; 
                }
            } else {
                echo "Failed to demote previous committee member";
            }
        }
    } else {
        echo "Not permitted to update committee members";
    }
}
$link->close();
?>