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
    }
    
    if (isset($_POST["event_id"])) {
        $event_id = $link->real_escape_string($_POST["event_id"]);

        $event_role_sql =   "SELECT UsersEvent_Role
                            FROM users_events
                            WHERE User_ID='$user_id'
                            AND Event_ID='$event_id'";

        $event_role = $link->query($event_role_sql)->fetch_assoc();
        $event_role = $event_role["UsersEvent_Role"];
    }

    if (((isset($club_id) && $club_role <= 2) || (isset($event_id) && $event_role <= 3)) && !isAdmin()) {
        // Validation
        if (isset($_FILES["image"])) {
            $logo = $_FILES["image"];

            $allowed_types = array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP);
            $file_type = exif_imagetype($logo["tmp_name"]);
            if (!in_array($file_type, $allowed_types)) {
                echo "File type not permitted, please upload only .jpg, .png or .webp\n";
                $error = true;
            }
        } else {
            echo "No file selected\n";
            $error = true;
        }

        if (!$error) {
            if (isset($club_id)) {
                $logo_path = $_SERVER["DOCUMENT_ROOT"] . "/sdp/resource/images/clublogo/";
            } elseif (isset($event_id)) {
                $logo_path = $_SERVER["DOCUMENT_ROOT"] . "/sdp/resource/images/eventlogo/";
            }

            // Delete existing image
            if (isset($club_id)) {
                $current_logo_sql = "SELECT Club_Logo AS Logo FROM clubs WHERE Club_ID='$club_id'";
            } elseif (isset($event_id)) {
                $current_logo_sql = "SELECT Event_Logo AS Logo FROM events WHERE Event_ID='$event_id'";
            }
            $current_logo = $link->query($current_logo_sql)->fetch_assoc();

            if (@unlink($logo_path . $current_logo["Logo"])) {
                // Create image name
                if (isset($club_id)) {
                    $logo_name = $club_id . ".";
                } elseif (isset($event_id)) {
                    $logo_name = $event_id . ".";
                }
                switch ($file_type) {
                    case IMAGETYPE_JPEG:
                        $logo_name .= "jpg";
                        break;
                    case IMAGETYPE_PNG:
                        $logo_name .= "png";
                        break;
                    case IMAGETYPE_WEBP:
                        $logo_name .= "webp";
                        break;
                }

                // Upload new image
                if (move_uploaded_file($logo["tmp_name"], $logo_path . $logo_name)) {
                    if (isset($club_id)) {
                        $update_logo_sql = "UPDATE clubs SET Club_Logo='$logo_name' WHERE Club_ID='$club_id'";
                    } elseif (isset($event_id)) {
                        $update_logo_sql = "UPDATE events SET Event_Logo='$logo_name' WHERE Event_ID='$event_id'";
                    }
                    if ($link->query($update_logo_sql)) {
                        echo "Logo updated";
                    } else {
                        echo "Failed to update logo";
                    }
                } else {
                    echo "Failed to upload logo";
                }
            } else {
                echo "Failed to delete old logo";
            }
        }
    } else {
        echo "Not permitted to update logo";
    }
}
$link->close();
?>