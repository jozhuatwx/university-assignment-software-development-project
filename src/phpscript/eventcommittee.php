<?php
include_once("../server.php");
include_once("event.php");

if (isLoggedIn()) {
    if ($event_role == 2 && strtotime($event_endday) >= strtotime("now") && $event_approval != "Canceled") {
        if (isset($_POST["role"])) {
            $role = $link->real_escape_string($_POST["role"]);

            if ($role == "Organiser") {
                $role = 2;
            } else if ($role == "Committee") {
                $role = 3;
            } elseif ($role == "Member") {
                $role = 4;
            } else {
                echo "Unknown role\n";
                $error = true;
            }

            if (isset($_POST["detail"])) {
                $detail = $link->real_escape_string($_POST["detail"]);
            } else {
                $detail = "";
            }
        } else {
            echo "Failed to get role\n";
            $error = true;
        }
        if (isset($_POST["user_id"])) {
            $committee_user_id = $link->real_escape_string($_POST["user_id"]);
        } else {
            echo "Failed to get User ID\n";
            $error = true;
        }
        
        if (!$error) {
            // Update
            $update_sql = "UPDATE users_events SET UsersEvent_Role=$role, UsersEvent_CommitteeRole='$detail' WHERE Event_ID='$event_id' AND User_ID='$committee_user_id'";
            if ($link->query($update_sql)) {
                if ($role == 2) {
                    $update_detail_sql = "UPDATE users_events SET UsersEvent_Role=3, UsersEvent_CommitteeRole=NULL WHERE Event_ID='$event_id' AND User_ID='$user_id'";
                    $link->query($update_detail_sql);

                    echo "You have been demoted to a regular committee";
                } else {
                    echo "Updated $committee_user_id's role";
                }
            } else {
                echo "Failed to update role";
            }
        }
    } elseif ($event_role != 2) {
        echo "Not permitted to update committee members";
    } elseif (strtotime($event_endday) >= strtotime("now")) {
        echo "This event has ended";
    } elseif ($event_approval != "Canceled") {
        echo "This event has been canceled";
    }
}
$link->close();
?>