<?php
include_once("../server.php");

if (isLoggedIn()) {
    if (isset($_POST["action"])) {
        $action = $_POST["action"];
    }

    // Validation
    if (isset($_POST["user_id"])) {
        $member_user_id = $link->real_escape_string($_POST["user_id"]);
    } else {
        echo "Failed to get member's User ID\n";
        $error = true;
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
            $event_name = $event["Event_Name"];
        } else {
            echo "Error retrieving event role";
            $error = true;
        }
    }
    
    if (!$error) {
        if (isset($club_id)) {
            $update_member_sql =    "UPDATE users_clubs SET UsersClub_Approval='Approved'
                                    WHERE User_ID='$member_user_id' AND Club_ID='$club_id'";
            $delete_member_sql =    "DELETE FROM users_clubs
                                    WHERE User_ID='$member_user_id' AND Club_ID='$club_id'";
                                    
            // Retrieve member email
            $member_email_sql = "SELECT users.User_EmailAddress1
                                FROM users
                                INNER JOIN users_clubs ON users.User_ID=users_clubs.User_ID
                                WHERE users_clubs.User_ID='$member_user_id' AND users_clubs.Club_ID='$club_id'";
            $member_email = $link->query($member_email_sql)->fetch_assoc();
            $recipent = $member_email["User_EmailAddress1"];

            if ($club_role == 2) {
                // Enrolment
                if ($action == "Approve") {
                    if ($link->query($update_member_sql)) {
                        echo "Approved " . $member_user_id;

                        // Email
                        $subject = "Enrolment Request Approved " . "(" . $club_name . ")";
                        $message = "Your request to join " . $club_name . " has been approved.";
                        mail($recipent, $subject, $message);
                    } else {
                        echo "Failed to approve " . $member_user_id;
                    }
                } elseif ($action == "Reject") {
                    if ($link->query($delete_member_sql)) {
                        echo "Rejected " . $member_user_id;

                        // Email
                        $subject = "Enrolment Request Rejected " . "(" . $club_name . ")";
                        $message = "Your request to join " . $club_name . " has been rejected.";
                        mail($recipent, $subject, $message);
                    } else {
                        echo "Failed to reject " . $member_user_id;
                    }
                }
            } elseif ($club_role == 1) {
                // Termination
                if ($action == "Approve" || $action == "Sack") {
                    $member_club_role_sql = "SELECT UsersClub_Role
                                            FROM users_clubs
                                            WHERE User_ID='$member_user_id'
                                            AND Club_ID='$club_id'";

                    $member_club_role = $link->query($member_club_role_sql)->fetch_assoc();
                    $member_club_role = $member_club_role["UsersClub_Role"];
                    if ($member_club_role != 2) {
                        if ($link->query($delete_member_sql)) {
                            if ($action == "Approve") {
                                echo "Approved " . $member_user_id;
                            } else {
                                echo "Sacked " . $member_user_id;
                            }

                            // Email
                            if ($action == "Approve") {
                                $subject = "Termination Request Approved (" . $club_name . ")";
                                $message = "Your request to leave " . $club_name . " has been approved.";
                            } else {
                                $subject = "Membership Terminated (" . $club_name . ")";
                                $message = "Your membership at " . $club_name . " has been revoked.";
                            }
                            mail($recipent, $subject, $message);
                        } else {
                            echo "Failed to approve " . $member_user_id;
                        }
                    } else {
                        echo "Please remove member from committee first";
                    }
                } elseif ($action == "Reject") {
                    if ($link->query($update_member_sql)) {
                        echo "Rejected " . $member_user_id;

                        // Email
                        $subject = "Termination Request Rejected " . "(" . $club_name . ")";
                        $message = "Your request to leave " . $club_name . " has been rejected.";
                        mail($recipent, $subject, $message);
                    } else {
                        echo "Failed to reject " . $member_user_id;
                    }
                }
            } else {
                echo "Not permitted to approve/ reject requests";
            }
        } elseif (isset($event_id)) {
            $update_member_sql =    "UPDATE users_events SET UsersEvent_Approval='Approved'
                                    WHERE User_ID='$member_user_id' AND Event_ID='$event_id'";
            $delete_member_sql =    "DELETE FROM users_events
                                    WHERE User_ID='$member_user_id' AND Event_ID='$event_id'";
            $member_status_sql =    "SELECT UsersEvent_Approval
                                    FROM users_events
                                    WHERE User_ID='$member_user_id' AND Event_ID='$event_id'";
            $member_status = $link->query($member_status_sql)->fetch_assoc();
            $member_status = $member_status["UsersEvent_Approval"];
                        
            // Retrieve member email
            $member_email_sql = "SELECT users.User_EmailAddress1
                                FROM users
                                INNER JOIN users_events ON users.User_ID=users_events.User_ID
                                WHERE users_events.User_ID='$member_user_id' AND users_events.Event_ID='$event_id'";
            $member_email = $link->query($member_email_sql)->fetch_assoc();
            $recipent = $member_email["User_EmailAddress1"];

            if (($event_role == 2 || $event_role == 3) && $member_status == "Pending") {
                if ($action == "Approve") {
                    if ($link->query($update_member_sql)) {
                        echo "Approved " . $member_user_id;

                        // Email
                        $subject = "Enrolment Request Approved " . "(" . $event_name . ")";
                        $message = "Your request to join " . $event_name . " has been approved.";
                        mail($recipent, $subject, $message);
                    } else {
                        echo "Failed to approve " . $member_user_id;
                    }
                } elseif ($action == "Reject") {
                    if ($link->query($delete_member_sql)) {
                        echo "Rejected " . $member_user_id;

                        // Email
                        $subject = "Enrolment Request Rejected " . "(" . $event_name . ")";
                        $message = "Your request to join " . $event_name . " has been rejected.";
                        mail($recipent, $subject, $message);
                    } else {
                        echo "Failed to reject " . $member_user_id;
                    }
                }
            } elseif (($event_role == 2 || $event_role == 3) && $member_status == "Terminate") {
                $member_event_role_sql =    "SELECT UsersEvent_Role
                                            FROM users_events
                                            WHERE User_ID='$member_user_id'
                                            AND Event_ID='$event_id'";

                $member_event_role = $link->query($member_event_role_sql)->fetch_assoc();
                $member_event_role = $member_event_role["UsersEvent_Role"];
                if ($action == "Approve") {
                    if ($member_event_role == 4) {
                        if ($link->query($delete_member_sql)) {
                            echo "Approved " . $member_user_id;
    
                            // Email
                            $subject = "Termination Request Approved (" . $event_name . ")";
                            $message = "Your request to leave " . $event_name . " has been approved.";
                            mail($recipent, $subject, $message);
                        } else {
                            echo "Failed to approve " . $member_user_id;
                        }
                    } elseif ($member_event_role == 3) {
                        echo "Please remove member from committee first";
                    } elseif ($member_event_role == 2) {
                        echo "Please relinquish your role as organiser to another member";
                    }
                } elseif ($action == "Reject") {
                    if ($link->query($update_member_sql)) {
                        echo "Rejected " . $member_user_id;

                        // Email
                        $subject = "Termination Request Rejected (" . $event_name . ")";
                        $message = "Your request to leave " . $event_name . " has been rejected.";
                        mail($recipent, $subject, $message);
                    } else {
                        echo "Failed to reject " . $member_user_id;
                    }
                }
            } elseif (($event_role == 2 || $event_role == 3) && $member_status == "Approved") {
                if ($action == "Sack") {
                    $member_event_role_sql =    "SELECT UsersEvent_Role
                                                FROM users_events
                                                WHERE User_ID='$member_user_id'
                                                AND Event_ID='$event_id'";

                    $member_event_role = $link->query($member_event_role_sql)->fetch_assoc();
                    $member_event_role = $member_event_role["UsersEvent_Role"];
                    
                    if ($member_event_role == 4) {
                        if ($link->query($delete_member_sql)) {
                            echo "Sacked " . $member_user_id;
                            
                            // Email
                            $subject = "Membership Terminated (" . $event_name . ")";
                            $message = "Your membership at " . $event_name . " has been revoked.";
                            mail($recipent, $subject, $message);
                        }
                    } elseif ($member_event_role == 3) {
                        echo "Please remove member from committee first";
                    } elseif ($member_event_role == 2) {
                        echo "Please relinquish your role as organiser to another member";
                    }
                }
            }
        }
    }
}
$link->close();
?>