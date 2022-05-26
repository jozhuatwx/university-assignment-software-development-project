<?php
include_once("../server.php");

if (isLoggedIn()) {
    if (isset($_POST["action"])) {
        $action = $_POST["action"];
    }

    // Validation
    if (isset($_POST["event_id"])) {
        $event_id = $link->real_escape_string($_POST["event_id"]);
        
        $event_role_sql =   "SELECT UsersEvent_Role
                            FROM users_events
                            WHERE User_ID='$user_id'
                            AND Event_ID='$event_id'";

        if ($event_role = $link->query($event_role_sql)->fetch_assoc()) {
            $event_role = $event_role["UsersEvent_Role"];
        } else {
            $event_role = 6;
        }

        $event_name_sql =    "SELECT Event_Name
                            FROM events
                            WHERE Event_ID='$event_id'";
        $event_name = $link->query($event_name_sql)->fetch_assoc();
        $event_name = $event_name["Event_Name"];
    } else {
        echo "Failed to get Event ID";
        $error = true;
    }

    if ($action == "Follow") {
        if (empty($event_role) || $event_role >= 6) {
            $follow_sql = "INSERT INTO users_events VALUES ('$user_id', '$event_id', 'Pending', 5, NULL)";
            if ($link->query($follow_sql)) {
                echo "Following event";
            } else {
                echo "Failed to follow event";
            }
        } else {
            echo "User is already following";
        }
    } elseif ($action == "Unfollow") {
        if ($event_role == 5) {
            $unfollow_sql = "DELETE FROM users_events WHERE User_ID='$user_id' AND Event_ID='$event_id'";
            if ($link->query($unfollow_sql)) {
                echo "Unfollowed event";
            } else {
                echo "Failed to unfollow event";
            }
        } elseif ($event_role < 5) {
            echo "You cannot unfollow the event";
        } elseif ($event_role > 5) {
            echo "You are not following the event";
        }
    } elseif ($action == "Volunteer") {
        if ($event_role == 5) {
            $volunteer_sql = "UPDATE users_events SET UsersEvent_Role=4 WHERE User_ID='$user_id' AND Event_ID='$event_id'";
        } elseif ((empty($event_role) || $event_role >= 6) && !isAdvisor()) {
            $volunteer_sql = "INSERT INTO users_events VALUES ('$user_id', '$event_id', 'Pending', 4, NULL)";
        } elseif ($event_role <= 4) {
            echo "You are already in the event";
        } elseif (isAdvisor()) {
            echo "Please request the administrator to reassign your event(s)";
        }

        if (isset($volunteer_sql)) {
            if ($link->query($volunteer_sql)) {
                echo "Requested to volunteer event";
    
                // Retrieve committee emails
                $committee_emails_sql = "SELECT users.User_EmailAddress1
                                        FROM users
                                        INNER JOIN users_events ON users.User_ID=users_events.User_ID
                                        WHERE users_events.Event_ID='$event_id' AND users_events.UsersEvent_Role=2 OR users_events.UsersEvent_Role=3";
                $committee_emails = $link->query($committee_emails_sql);
    
                $recipent = "";
                // Email
                while ($committee_email = $committee_emails->fetch_assoc()) {
                    if (!empty($committee_email["User_EmailAddress1"])) {
                        $recipent .= $committee_email["User_EmailAddress1"] . ", ";
                    }
                }
                $subject = "Enrolment Request " . "(" . $event_name . ")";
                $message = $_SESSION["first_name"] . " " . $_SESSION["last_name"] . " (" . $user_id . ") has requested to volunteer for " . $event_name . ".";
                $message .= "\r\nContact Number: " . $_SESSION["contact_number1"];
                $message .= "\r\nEmail Address: " . $_SESSION["email1"];
                mail($recipent, $subject, $message);
            } else {
                echo "Failed to request to volunteer event";
            }
        }
    } elseif ($action == "CancelVolunteer") {
        $cancel_volunteer_sql = "DELETE FROM users_events WHERE User_ID='$user_id' AND Event_ID='$event_id'";
        if ($link->query($cancel_volunteer_sql)) {
            echo "Canceled volunteer request";
        } else {
            echo "Failed to cancel volunteer request";
        }
    } elseif ($action == "Leave") {
        if ($event_role == 4 || $event_role == 3) {
            $leave_sql = "UPDATE users_events SET UsersEvent_Approval='Terminate' WHERE User_ID='$user_id' AND Event_ID='$event_id'";
            if ($link->query($leave_sql)) {
                echo "Requested to leave event";

                // Retrieve committee emails
                $committee_emails_sql = "SELECT users.User_EmailAddress1
                                        FROM users
                                        INNER JOIN users_events ON users.User_ID=users_events.User_ID
                                        WHERE users_events.Event_ID='$event_id' AND users_events.UsersEvent_Role=2";
                $committee_emails = $link->query($committee_emails_sql);

                $recipent = "";
                // Email
                while ($committee_email = $committee_emails->fetch_assoc()) {
                    if (!empty($committee_email["User_EmailAddress1"])) {
                        $recipent .= $committee_email["User_EmailAddress1"] . ", ";
                    }
                }
                $subject = "Termination Request " . "(" . $event_name . ")";
                $message = $_SESSION["first_name"] . " " . $_SESSION["last_name"] . " (" . $user_id . ") has requested to leave " . $event_name . ".";
                $message .= "\r\nContact Number: " . $_SESSION["contact_number1"];
                $message .= "\r\nEmail Address: " . $_SESSION["email1"];
                mail($recipent, $subject, $message);
            } else {
                echo "Failed to request to leave event";
            }
        } elseif ($event_role == 2) {
            echo "Please relinquish your role as organiser to another member";
        } elseif ($event_role == 1) {
            echo "Advisors cannot leave event";
        }
    } elseif ($action == "CancelLeave") {
        $cancel_leave_sql = "UPDATE users_events SET UsersEvent_Approval='Approved' WHERE User_ID='$user_id' AND Event_ID='$event_id'";
        if ($link->query($cancel_leave_sql)) {
            echo "Canceled leave request";
        } else {
            echo "Failed to cancel leave request";
        }
    }
}
$link->close();
?>