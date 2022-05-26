<?php
include_once("../server.php");

// Check if user is authorised
if (isAdmin()) {
    if (isset($_POST["user_id"])) {
        $advisor_user_id = $link->real_escape_string($_POST["user_id"]);
    } else {
        echo "Failed to get advisor's User ID\n";
        $error = true;
    }
    if (isset($_POST["clubs"])) {
        $clubs = $link->real_escape_string($_POST["clubs"]);
    } else {
        echo "Failed to get advisor's selected clubs\n";
        $error = true;
    }

    if (!$error) {
        // Get current clubs
        $current_clubs_sql =    "SELECT clubs.Club_Name
                                FROM users_clubs
                                INNER JOIN clubs ON users_clubs.Club_ID=clubs.Club_ID
                                WHERE User_ID = '$advisor_user_id'";
        if ($current_clubs = $link->query($current_clubs_sql)) {
            $current_clubs_array = array();
            while ($current_club = $current_clubs->fetch_assoc()) {
                $current_clubs_array[] = $current_club["Club_Name"];
            }

            // Change selected clubs into array
            $selected_clubs_array = array_filter(explode(", ", $clubs));

            // Removed selected current clubs
            // Find duplicated data
            $intersect_club_array = array_intersect($selected_clubs_array, $current_clubs_array);

            foreach ($intersect_club_array as $intersect_club) {
                // Delete duplication
                $current_index = array_search($intersect_club, $current_clubs_array);
                if ($current_index !== FALSE) {
                    unset($current_clubs_array[$current_index]);
                }

                $select_index = array_search($intersect_club, $selected_clubs_array);
                if ($select_index !== FALSE) {
                    unset($selected_clubs_array[$select_index]);
                }
            }

            // SQL delete deselected clubs
            if ($current_clubs_array && !empty($current_clubs_array)) {
                $prepared_delete = $link->prepare("DELETE FROM users_clubs WHERE User_ID=? AND Club_ID=(SELECT Club_ID FROM clubs WHERE Club_Name=?)");
                $prepared_delete->bind_param("ss", $advisor_user_id, $club_name);

                foreach ($current_clubs_array as $current_club) {
                    $club_name = $current_club;
                    if ($prepared_delete->execute()) {
                        echo $club_name . " removed from " . $advisor_user_id;
                    } else {
                        echo "Failed to remove " . $club_name . " from " . $advisor_user_id;
                    }
                }

                $prepared_delete->close();

                // Check if there's any clubs left
                $no_clubs_sql = "SELECT User_ID FROM users_clubs WHERE User_ID='$advisor_user_id'";
                $no_clubs_result = $link->query($no_clubs_sql);

                // Create if there is none left
                if ($no_clubs_result->num_rows < 1) {
                    $create_role_sql = "INSERT INTO users_clubs VALUES ('$advisor_user_id', NULL, 'Approved', 1, NULL)";
                    if (!$link->query($create_role_sql)) {
                        echo "Please re-assign a club to " . $advisor_user_id;
                    }
                }
            }

            // SQL insert selected clubs
            if ($selected_clubs_array && !empty($selected_clubs_array)) {
                // Check if there is null
                $null_clubs_sql = "SELECT User_ID FROM users_clubs WHERE User_ID='$advisor_user_id' AND Club_ID IS NULL";
                $null_clubs_result = $link->query($null_clubs_sql);

                // Delete if there is null
                if ($null_clubs_result->num_rows >= 1) {
                    $delete_null_sql = "DELETE FROM users_clubs WHERE User_ID='$advisor_user_id'";
                    $link->query($delete_null_sql);
                }

                $prepared_insert = $link->prepare("INSERT INTO users_clubs VALUES (?, (SELECT Club_ID FROM clubs WHERE Club_Name=?), 'Approved', 1, NULL)");
                $prepared_insert->bind_param("ss", $advisor_user_id, $club_name);

                $prepared_check_events = $link->prepare("SELECT Event_ID FROM events WHERE Club_ID=(SELECT Club_ID FROM clubs WHERE Club_Name=?) AND Event_StartDateTime>=CURRENT_DATE AND NOT (Event_Approval='Updating' OR Event_Approval='Canceled')");
                $prepared_check_events->bind_param("s", $club_name);

                $prepared_insert_events = $link->prepare("INSERT INTO users_events VALUES (?, ?, 'Approved', 1, '')");
                $prepared_insert_events->bind_param("ss", $advisor_user_id, $event_id);

                foreach ($selected_clubs_array as $selected_club) {
                    $club_name = $selected_club;
                    if ($prepared_insert->execute()) {
                        echo $club_name . " added to " . $advisor_user_id;

                        $prepared_check_events->execute();
                        $check_events = $prepared_check_events->get_result();

                        while ($event = $check_events->fetch_assoc()) {
                            $event_id = $event["Event_ID"];
                            $prepared_insert_events->execute();
                        }
                    } else {
                        echo "Failed to add " . $club_name . " to " . $advisor_user_id;                    
                    };
                }
                $prepared_insert_events->close();
                $prepared_check_events->close();
                $prepared_insert->close();
            }
        } else {
            echo "Server failed";
        }
    }
} else {
    echo "Not permitted to update advisors' assigned clubs";
}
$link->close();
?>