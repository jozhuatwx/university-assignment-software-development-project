<?php
include_once("../server.php");

if (isLoggedIn()) {
    if (isset($_POST["action"])) {
        $action = $_POST["action"];
    }
    if (isset($_POST["title"])) {
        $announcement_title = $link->real_escape_string($_POST["title"]);
    }
    if (isset($_POST["statement"])) {
        $announcement_statement = $link->real_escape_string($_POST["statement"]);
    }
    if (isset($_POST["announcement_id"])) {
        $announcement_id = $link->real_escape_string($_POST["announcement_id"]);
    }
    if (isset($_POST["club_id"])) {
        $club_id = "'" . $link->real_escape_string($_POST["club_id"]) . "'";

        $club_role_sql =    "SELECT UsersClub_Role
                            FROM users_clubs
                            WHERE User_ID='$user_id'
                            AND Club_ID=$club_id";

        $club_role = $link->query($club_role_sql)->fetch_assoc();
        $club_role = $club_role["UsersClub_Role"];
    } else {
        $club_id = "NULL";
    }
    if (isset($_POST["event_id"])) {
        $event_id = "'" . $link->real_escape_string($_POST["event_id"]) . "'";

        $event_role_sql =    "SELECT UsersEvent_Role
                            FROM users_events
                            WHERE User_ID='$user_id'
                            AND Event_ID=$event_id";

        $event_role = $link->query($event_role_sql)->fetch_assoc();
        $event_role = $event_role["UsersEvent_Role"];
    } else {
        $event_id = "NULL";
    }
    if (isset($_POST["public_announcement"])) {
        $public_announcement = $link->real_escape_string($_POST["public_announcement"]);
    } elseif (isAdmin()) {
        $public_announcement = 1;
    } else {
        $public_announcement = 0;
    }
    $datetime = date("Y-m-d H:i:s");

    // Validation
    if ($action == "Post" || $action == "Update") {
        if (empty($announcement_title)) {
            echo "Announcement title cannot be empty\n";
            $error = true;
        }
        if (empty($announcement_statement)) {
            echo "Announcement statement cannot be empty\n";
            $error = true;
        }
        if (strlen($announcement_title) > 255) {
            echo "Announcement title cannot exceed 255 characters\n";
            $error = true;
        }
        if (strlen($announcement_statement) > 65535) {
            echo "Announcement content cannot exceed 65535 characters\n";
            $error = true;
        }
    }

    // Authorisation
    $authorised = false;
    if ($club_id != "NULL" && $club_role <= 2 && !isAdmin()) {
        $authorised = true;
    } elseif ($event_id != "NULL" && $event_role <= 3 && !isAdmin()) {
        $authorised = true;
    } elseif ($club_id == "NULL" && $event_id == "NULL" && isAdmin()) {
        $authorised = true;
    }

    if ($authorised && $action == "Post" && !$error) {
        // Get announcement ID
        $get_id_sql =   "SELECT MAX(Announcement_ID) AS Max_ID
                        FROM announcements";
        $get_id = $link->query($get_id_sql);
        $get_id = $get_id->fetch_assoc();

        if ($get_id["Max_ID"]) {
            $announcement_id = "AN" . str_pad(trim($get_id["Max_ID"], "AN") + 1, 6, "0", STR_PAD_LEFT);
        } else {
            $announcement_id = "AN000000";
        }

        // Post club announcement
        $insert_announcement_sql =  "INSERT INTO announcements
                                    VALUES ('$announcement_id', '$announcement_title', '$announcement_statement', '$datetime', $public_announcement, $club_id, $event_id)";
        if ($link->query($insert_announcement_sql)) {
            $notification = "Posted";
            if ($public_announcement) {
                $notification .= " public";
            }
            if (isset($club_role)) {
                $notification .= " club";
            } elseif (isset($event_role)) {
                $notification .= " event";
            }
            $notification .= " announcement";
            echo $notification;
        } else {
            echo "Failed to post announcement";
        }
    } elseif ($authorised && $action == "Update" && !$error) {
        $update_announcement_sql = "UPDATE announcements
                                    SET Announcement_Title='$announcement_title', Announcement_Statement='$announcement_statement', Announcement_Public=$public_announcement
                                    WHERE Announcement_ID='$announcement_id'";
        // Update club announcement
        if ($link->query($update_announcement_sql)) {
            $notification = "Updated";
            if ($public_announcement) {
                $notification .= " public";
            }
            if (isset($club_role)) {
                $notification .= " club";
            } elseif (isset($event_role)) {
                $notification .= " event";
            }
            $notification .= " announcement";
            echo $notification;
        } else {
            echo "Failed to update announcement";
        }
    } elseif ($authorised && $action == "Delete") {
        $delete_announcement_sql = "DELETE FROM announcements
                                    WHERE Announcement_ID='$announcement_id'";
        // Delete club announcement
        if ($link->query($delete_announcement_sql)) {
            $notification = "Deleted";
            if ($public_announcement) {
                $notification .= " public";
            }
            if (isset($club_role)) {
                $notification .= " club";
            } elseif (isset($event_role)) {
                $notification .= " event";
            }
            $notification .= " announcement";
            echo $notification;
        } else {
            echo "Failed to update announcement";
        }
    } elseif (!$authorised) {
        echo "Not authorised to post/update/delete announcement";
    }
}
$link->close();
?>