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

    if ($club_role <= 2 && !isAdmin()) {
        if (isset($_POST["club_day"])) {
            // Update Club Day
            $days = $link->real_escape_string($_POST["club_day"]);

            if (strlen($days) > 61) {
                echo "Please select from combobox only\n";
                $error = true;
            }

            if (empty($days)) {
                $update_day_sql =   "UPDATE clubs
                                    SET Club_Day='Not Fixed'
                                    WHERE Club_ID='$club_id'";
                $link->query($update_day_sql);
                echo "Please select a day\n";
                $error = true;
            }

            if (!$error) {
                $update_day_sql =   "UPDATE clubs
                                    SET Club_Day='$days'
                                    WHERE Club_ID='$club_id'";
                if ($link->query($update_day_sql)) {
                    echo "Updated club days to " . $days;
                } else {
                    echo "Failed to update club days";
                }
            }
        }
        
        if (isset($_POST["club_starttime"])) {
            // Update Club Time
            $starttime = $link->real_escape_string($_POST["club_starttime"]);
            $starttime = date("H:i:s", strtotime($starttime));

            if (empty($starttime)) {
                echo "Club start time cannot be empty\n";
                $error = true;
            }
        
            if (!$error) {
                $update_starttime_sql = "UPDATE clubs
                                        SET Club_StartTime='$starttime'
                                        WHERE Club_ID='$club_id'";
                if ($link->query($update_starttime_sql)) {
                    echo "Updated club start time to " . $starttime;
                } else {
                    echo "Failed to update club start time";
                }
            }
        }
        
        if (isset($_POST["club_endtime"])) {
            // Update Club Time
            $endtime = $link->real_escape_string($_POST["club_endtime"]);
            $endtime = date("H:i:s", strtotime($endtime));

            if (empty($endtime)) {
                echo "Club end time cannot be empty\n";
                $error = true;
            }
        
            if (!$error) {
                $update_endtime_sql =   "UPDATE clubs
                                        SET Club_EndTime='$endtime'
                                        WHERE Club_ID='$club_id'";
                if ($link->query($update_endtime_sql)) {
                    echo "Updated club end time to " . $endtime;
                } else {
                    echo "Failed to update club end time";
                }
            }
        }
        
        if (isset($_POST["club_location"])) {
            // Update Club Location
            $location = $link->real_escape_string($_POST["club_location"]);

            if (strlen($location) > 40) {
                echo "Location cannot exceed 40 characters\n";
                $error = true;
            }

            if (!$error) {
                $update_location_sql =  "UPDATE clubs
                                        SET Club_Location='$location'
                                        WHERE Club_ID='$club_id'";
                if ($link->query($update_location_sql)) {
                    echo "Updated club location to " . $location;
                } else {
                    echo "Failed to update club location";
                }
            }
        }
    } else {
        echo "Not permitted to update club details";
    }
}
$link->close();
?>