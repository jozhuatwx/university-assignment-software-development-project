<?php
include_once("../server.php");

if (isLoggedIn()) {
    if (isset($_POST["action"])) {
        $action = $_POST["action"];
    }

    if (isset($_POST["event_name"])) {
        $event_name = $link->real_escape_string($_POST["event_name"]);
    }
    if (isset($_POST["description"])) {
        $description = $link->real_escape_string($_POST["description"]);
    }
    if (isset($_POST["start_datetime"])) {
        $start_datetime = date("Y-m-d H:i:s", strtotime($link->real_escape_string($_POST["start_datetime"])));
    }
    if (isset($_POST["end_datetime"])) {
        $end_datetime = date("Y-m-d H:i:s", strtotime($link->real_escape_string($_POST["end_datetime"])));
    }
    if (isset($_POST["committee"])) {
        $committee = $link->real_escape_string($_POST["committee"]);
    }
    if (isset($_POST["committeerole"])) {
        $committeerole = $link->real_escape_string($_POST["committeerole"]);
    }
    if (isset($_POST["location"])) {
        $location = $link->real_escape_string($_POST["location"]);
    }
    if (isset($_POST["club_id"])) {
        $club_id = $link->real_escape_string($_POST["club_id"]);
        
        $clubs_sql =    "SELECT UsersClub_Role, Club_Name
                        FROM users_clubs
                        INNER JOIN clubs ON users_clubs.Club_ID=clubs.Club_ID
                        WHERE User_ID='$user_id' AND users_clubs.Club_ID='$club_id'";
        if ($club = $link->query($clubs_sql)) {
            $club = $club->fetch_assoc();
            $club_role = $club["UsersClub_Role"];
            $club_name = $club["Club_Name"];
        } else {
            echo "Error retrieving club role";
            $error = true;
        }
    }
    if (isset($_POST["event_id"])) {
        $event_id = $link->real_escape_string($_POST["event_id"]);

        $event_sql =    "SELECT UsersEvent_Role, Event_Name
                        FROM users_events
                        INNER JOIN events ON users_events.Event_ID=events.Event_ID
                        WHERE users_events.User_ID='$user_id' AND users_events.Event_ID='$event_id'";
        if ($event = $link->query($event_sql)) {
            $event = $event->fetch_assoc();
            $event_role = $event["UsersEvent_Role"];
            
            if ($action != "UpdateEvent") {
                $event_name = $event["Event_Name"];
            }
        } else {
            echo "Error retrieving event role";
            $error = true;
        }
    }

    // Validation
    if ($action == "CreateEvent" || $action == "UpdateEvent") {
        if (empty($event_name)) {
            echo "Event name cannot be empty\n";
            $error = true;
        }
        if (empty($description)) {
            echo "Description cannot be empty\n";
            $error = true;
        }
        if (empty($start_datetime)) {
            echo "Please select start date and time\n";
            $error = true;
        }
        if (empty($end_datetime)) {
            echo "Please select end date and time\n";
            $error = true;
        }
        if (empty($location)) {
            echo "Location cannot be empty\n";
            $error = true;
        }
        if ($action == "CreateEvent" && empty($club_id)) {
            echo "Cannot get Club ID\n";
            $error = true;
        }

        if (strlen($event_name) > 50) {
            echo "Event name cannot exceed 50 characters";
            $error = true;
        }
        if (strlen($description) > 65535) {
            echo "Description cannot exceed 65535 characters\n";
            $error = true;
        }
        if (strtotime($start_datetime) <= date("Y-m-d", strtotime("+1 month"))) {
            echo "Start date must be a month away\n";
            $error = true;
        }
        if (strtotime($start_datetime) >= strtotime($end_datetime)) {
            echo "End date cannot be equal or lesser than start date\n";
            $error = true;
        }
        if (strlen($location) > 65535) {
            echo "Location cannot exceed 65535 characters\n";
            $error = true;
        }
        
    } elseif ($action == "Approve" || $action == "Reject" || $action == "UpdateEvent" || $action == "CancelEvent") {
        if (empty($event_id)) {
            echo "Cannot get Event ID\n";
            $error = true;
        }
    }

    // Email
    if ($action == "CreateEvent" || $action == "UpdateEvent" || $action == "CancelEvent") {
        // Retrieve advisor emails
        $advisor_emails_sql =   "SELECT users.User_EmailAddress1
                                FROM users
                                INNER JOIN users_clubs ON users.User_ID=users_clubs.User_ID
                                WHERE users_clubs.UsersClub_Role=1";
        $advisor_emails = $link->query($advisor_emails_sql);

        $advisor_recipent = "";
        // Email
        while ($advisor_email = $advisor_emails->fetch_assoc()) {
            $advisor_recipent .= $advisor_email["User_EmailAddress1"] . ", ";
        }
    }
    if ($action == "Approve" || $action == "Reject") {
        // Retrieve committee emails
        $committee_emails_sql = "SELECT users.User_EmailAddress1
                                FROM users
                                INNER JOIN users_events ON users.User_ID=users_events.User_ID
                                WHERE users_events.UsersEvent_Role=2 OR users_events.UsersEvent_Role=3";
        $committee_emails = $link->query($committee_emails_sql);

        $committee_recipent = "";
        // Email
        while ($committee_email = $committee_emails->fetch_assoc()) {
            $committee_recipent .= $committee_email["User_EmailAddress1"] . ", ";
        }

        $emails_sql =   "SELECT User_EmailAddress1
                        FROM users
                        INNER JOIN users_events ON users.User_ID=users_events.User_ID
                        WHERE users_events.Event_ID='$event_id'
                        AND users_events.UsersEvent_Role>2";
        $emails = $link->query($emails_sql);

        $all_recipent = "";
        // Email
        while ($email = $emails->fetch_assoc()) {
            $all_recipent .= $email["User_EmailAddress1"] . ", ";
        }
    }

    if ($action == "CreateEvent" && !$error && $club_role == 2 && !isAdmin()) {
        $event_id_sql = "SELECT MAX(Event_ID) FROM events WHERE Event_ID LIKE 'EV%'";
        $event_id = $link->query($event_id_sql)->fetch_assoc();

        if ($event_id["MAX(Event_ID)"]) {
            $event_id = "EV" . str_pad(trim($event_id["MAX(Event_ID)"], "EV") + 1, 8, 0, STR_PAD_LEFT);
        } else {
            $event_id = "EV00000000";
        }

        $logo_name = $event_id . ".png";
        $logo_path = $_SERVER["DOCUMENT_ROOT"] . "/sdp/resource/images/";
        copy($logo_path . "default/NewEvent.png", $logo_path . "eventlogo/" . $logo_name);

        $insert_sql =   "INSERT INTO events
                        VALUES ('$event_id', '$event_name', '$description', '$start_datetime', '$end_datetime', '$location', 'Pending', '$logo_name', '$club_id', NULL)";
        if ($link->query($insert_sql)) {
            $advisor_ids_sql = "SELECT User_ID FROM users_clubs WHERE UsersClub_Role=1 AND Club_ID='$club_id'";
            $advisor_ids = $link->query($advisor_ids_sql);

            $committee_ids = array();
            if (isset($committee)) {
                $committees = array_filter(explode(", ", $committee));
                $committeeroles = array_filter(explode(",", $committeerole));

                $i = 0;
                foreach ($committees as $committee) {
                    if (isset($committeeroles[$i]) && !empty(trim($committeeroles[$i]))) {
                        $committee_ids[substr($committee, -9, -1)] = trim($committeeroles[$i]);
                    } else {
                        $committee_ids[substr($committee, -9, -1)] = "";
                    }
                    $i++;
                }
            }

            $prepared_committee = $link->prepare("INSERT INTO users_events VALUES (?, '$event_id', 'Approved', ?, ?)");
            $prepared_committee->bind_param("sis", $id, $role, $roledetail);

            while ($ids = $advisor_ids->fetch_assoc()) {
                $id = $ids["User_ID"];
                $role = 1;
                $roledetail = "";
                $prepared_committee->execute();
            }

            $id = $user_id;
            $role = 2;
            $roledetail = "Organiser";
            $prepared_committee->execute();

            foreach ($committee_ids as $id => $roledetail) {
                $role = 3;
                $prepared_committee->execute();
            }

            $prepared_committee->close();

            // Retrieve committee emails
            $committee_emails_sql = "SELECT users.User_EmailAddress1
                                    FROM users
                                    INNER JOIN users_clubs ON users.User_ID=users_clubs.User_ID
                                    WHERE users_clubs.UsersClub_Role=2";
            $committee_emails = $link->query($committee_emails_sql);

            $committee_recipent = "";
            // Email
            while ($committee_email = $committee_emails->fetch_assoc()) {
                $committee_recipent .= $committee_email["User_EmailAddress1"] . ", ";
            }

            echo "New event pending approval";

            $subject = "New Event Requested";
            $message = "$user_id from $club_name has requested to create an event: $event_name";
            $message .= "\r\nDate & Time: $start_datetime - $end_datetime";
            $message .= "\r\nLocation: $location";
            mail($advisor_recipent, $subject, $message);

            $message .= "\r\nYou are part of the committee!";
            mail($committee_recipent, $subject, $message);
        } else {
            echo "Failed to request to create event";
        }
    } elseif ($action == "Approve" && !$error && $club_role == 1 && !isAdmin()) {
        $status_sql = "SELECT Event_Approval FROM events WHERE Event_ID='$event_id'";
        $status = $link->query($status_sql)->fetch_assoc();
        $status = $status["Event_Approval"];
        $error = false;
        if ($status == "Pending") {
            $approve_sql = "UPDATE events SET Event_Approval='Approved' WHERE Event_ID='$event_id'";
            if ($link->query($approve_sql)) {
                $type="Create";
            } else {
                echo "Failed to create event";
                $error = true;
            }
        } elseif ($status == "Updating") {
            $updateevent_sql = "SELECT * FROM events WHERE Event_ID='$event_id'";
            $updateevent = $link->query($updateevent_sql)->fetch_assoc();
            $event_name = $updateevent["Event_Name"];
            $description = $updateevent["Event_Description"];
            $start_datetime = $updateevent["Event_StartDateTime"];
            $end_datetime = $updateevent["Event_EndDateTime"];
            $location = $updateevent["Event_Location"];
            $old_id = $updateevent["Old_ID"];

            $updateoldevent_sql = "UPDATE events SET Event_Name='$event_name', Event_Description='$description', Event_StartDateTime='$start_datetime', Event_EndDateTime='$end_datetime', Event_Location='$location' WHERE Event_ID='$old_id'";
            if ($link->query($updateoldevent_sql)) {
                $type = "Update";

                $link->query("DELETE FROM users_events WHERE Event_ID='$event_id'");

                $subject = "$event_name Has Been Updated";
                $message = "$event_name has been updated by the event's committee and approved by the club advisor.";
                $message .= "\r\nPlease view the event page for more details/";
                mail($all_recipent, $subject, $message);
                
                $deletenewevent_sql = "DELETE FROM events WHERE Event_ID='$event_id'";
                $link->query($deletenewevent_sql);
            } else {
                echo "Failed to update event";
                $error = true;
            }
        } elseif ($status == "Canceling") {
            $approve_sql = "UPDATE events SET Event_Approval='Canceled' WHERE Event_ID='$event_id'";
            if ($link->query($approve_sql)) {
                $type = "Cancel";

                $link->query("DELETE FROM events WHERE Old_ID='$event_id'");

                $subject = "$event_name Has Been Canceled";
                $message = "$event_name has been canceled by the event's committee and approved by the club advisor.";
                mail($all_recipent, $subject, $message);
            } else {
                echo "Failed to cancel event";
                $error = true;
            }
        }

        if (isset($type) && isset($event_name) && !$error) {
            echo "Event request approved";

            $subject = "$type $event_name Approved";
            $message = "$type $event_name request has been approved by the club advisor.";
            mail($committee_recipent, $subject, $message);
        }
    } elseif ($action == "Reject" && !$error && $club_role == 1 && !isAdmin()) {
        $status_sql = "SELECT Event_Approval FROM events WHERE Event_ID='$event_id'";
        $status = $link->query($status_sql)->fetch_assoc();
        $status = $status["Event_Approval"];
        $error = false;

        if ($status == "Pending") {
            $delete_sql = "DELETE FROM events WHERE Event_ID='$event_id'";
            if ($link->query($delete_sql)) {
                $type = "Create";
            } else {
                echo "Failed to reject event";
                $error = true;
            }
        } elseif ($status == "Updating") {
            $oldevent_sql = "SELECT Event_Name FROM events WHERE Event_ID=(SELECT Old_ID FROM events WHERE Event_ID='$event_id')";
            $oldevent = $link->query($oldevent_sql)->fetch_assoc();
            
            $delete_sql = "DELETE FROM events WHERE Event_ID='$event_id'";
            if ($link->query($delete_sql)) {
                $link->query("DELETE FROM users_events WHERE Event_ID='$event_id'");

                $type = "Update";
                $event_name = $oldevent["Event_Name"];
            } else {
                echo "Failed to reject update";
                $error = true;
            }
        } elseif ($status == "Canceling") {
            $update_sql = "UPDATE events SET Event_Approval='Approved' WHERE Event_ID='$event_id'";
            if ($link->query($update_sql)) {
                $type = "Cancel";
            } else {
                echo "Failed to reject cancel";
                $error = true;
            }
        }

        if (isset($type) && isset($event_name) && !$error) {
            echo "Event request rejected";

            $subject = "$type $event_name Rejected";
            $message = "$type $event_name request has been rejected by the club advisor.";
            mail($committee_recipent, $subject, $message);
        }
    } elseif ($action == "UpdateEvent" && !$error && ($event_role == 2 || $event_role == 3) && !isAdmin()) {
        $oldevent_sql = "SELECT Event_Name, Club_ID FROM events WHERE Event_ID='$event_id'";
        $oldevent = $link->query($oldevent_sql)->fetch_assoc();
        $club_id = $oldevent["Club_ID"];
        
        $newevent_id_sql = "SELECT MAX(Event_ID) FROM events WHERE Event_ID LIKE 'UE%'";
        $newevent_id = $link->query($newevent_id_sql)->fetch_assoc();

        if ($newevent_id["MAX(Event_ID)"]) {
            $newevent_id = "UE" . str_pad(trim($newevent_id["MAX(Event_ID)"], "UE") + 1, 8, 0, STR_PAD_LEFT);
        } else {
            $newevent_id = "UE00000000";
        }
        
        $insert_sql =   "INSERT INTO events
                        VALUES ('$newevent_id', '$event_name', '$description', '$start_datetime', '$end_datetime', '$location', 'Updating', 'None', '$club_id', '$event_id')";
        if ($link->query($insert_sql)) {
            echo "Update event pending approval";

            $link->query("INSERT INTO users_events VALUE ('$user_id', '$newevent_id', 'Pending', 2, NULL)");

            $subject = "Update Event Requested";
            $message = "$user_id has requested to update the event: " . $oldevent["Event_Name"];
            mail($advisor_recipent, $subject, $message);
        } else {
            echo "Failed to request to update event";
        }
    } elseif ($action == "CancelEvent" && !$error && $event_role == 2 && !isAdmin()) {
        $update_sql = "UPDATE events SET Event_Approval='Canceling' WHERE Event_ID='$event_id'";
        if ($link->query($update_sql)) {
            echo "Cancel event pending approval";

            $subject = "Cancel Event Requested";
            $message = "$user_id has requested to cancel the event: " . $event_name;
            mail($advisor_recipent, $subject, $message);            
        } else {
            echo "Failed to request to cancel event";
        }
    }
}
$link->close();
?>