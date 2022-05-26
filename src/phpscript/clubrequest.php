<?php
include_once("../server.php");

if (isLoggedIn()) {
    if (isset($_POST["action"])) {
        $action = $_POST["action"];
    }

    // Validation
    if (isset($_POST["club_id"])) {
        $club_id = $link->real_escape_string($_POST["club_id"]);
        
        $club_role_sql =    "SELECT UsersClub_Role
                            FROM users_clubs
                            WHERE User_ID='$user_id'
                            AND Club_ID='$club_id'";

        $club_role = $link->query($club_role_sql)->fetch_assoc();
        $club_role = $club_role["UsersClub_Role"];

        $club_name_sql =    "SELECT Club_Name
                            FROM clubs
                            WHERE Club_ID='$club_id'";
        $club_name = $link->query($club_name_sql)->fetch_assoc();
        $club_name = $club_name["Club_Name"];
    } else {
        echo "Failed to get Club ID";
        $error = true;
    }

    if ($action == "Join") {
        if ($club_role < 3 && !isAdvisor()) {
            $join_sql = "INSERT INTO users_clubs VALUES ('$user_id', '$club_id', 'Pending', '3', NULL)";
            if ($link->query($join_sql)) {
                echo "Requested to join club";

                // Retrieve committee emails
                $committee_emails_sql = "SELECT users.User_EmailAddress1, users_clubs.UsersClub_CommitteeRole
                                        FROM users
                                        INNER JOIN users_clubs ON users.User_ID=users_clubs.User_ID
                                        WHERE users_clubs.Club_ID='$club_id' AND users_clubs.UsersClub_Role=2";
                $committee_emails = $link->query($committee_emails_sql);

                // Email
                while ($committee_email = $committee_emails->fetch_assoc()) {
                    if (!empty($committee_email["User_EmailAddress1"])) {
                        if ($committee_email["UsersClub_CommitteeRole"] == "C2") {
                            $recipent = $committee_email["User_EmailAddress1"];
                        } else {
                            $mail_headers .= "CC: " . $committee_email["User_EmailAddress1"] . "\r\n";
                        }
                    }
                }
                $subject = "Enrolment Request " . "(" . $club_name . ")";
                $message = $_SESSION["first_name"] . " " . $_SESSION["last_name"] . " (" . $user_id . ") has requested to join " . $club_name . ".";
                $message .= "\r\nContact Number: " . $_SESSION["contact_number1"];
                $message .= "\r\nEmail Address: " . $_SESSION["email1"];
                @mail($recipent, $subject, $message, $mail_headers);
            } else {
                echo "Failed to request to join club";
            }
        } elseif ($club_role == 3 || $club_role == 2) {
            echo "You are already in the club";
        } elseif (isAdvisor()) {
            echo "Please request the administrator to reassign your club(s)";
        }
    } elseif ($action == "CancelJoin") {
        $cancel_join_sql = "DELETE FROM users_clubs WHERE User_ID='$user_id' AND Club_ID='$club_id'";
        if ($link->query($cancel_join_sql)) {
            echo "Canceled join request";
        } else {
            echo "Failed to cancel join request";
        }
    } elseif ($action == "Leave") {
        if ($club_role == 3 || $club_role == 2) {
            $leave_sql = "UPDATE users_clubs SET UsersClub_Approval='Terminate' WHERE User_ID='$user_id' AND Club_ID='$club_id'";
            if ($link->query($leave_sql)) {
                echo "Requested to leave club";

                // Retrieve advisor emails
                $advisor_emails_sql =   "SELECT users.User_EmailAddress1
                                        FROM users
                                        INNER JOIN users_clubs ON users.User_ID=users_clubs.User_ID
                                        WHERE users_clubs.UsersClub_Role=1";
                $advisor_emails = $link->query($advisor_emails_sql);

                $recipent = "";
                // Email
                while ($advisor_email = $advisor_emails->fetch_assoc()) {
                    $recipent .= $advisor_email["User_EmailAddress1"] . ", ";
                }
                $subject = "Termination Request " . "(" . $club_name . ")";
                $message = $_SESSION["first_name"] . " " . $_SESSION["last_name"] . " (" . $user_id . ") has requested to leave " . $club_name . ".";
                $message .= "\r\nContact Number: " . $_SESSION["contact_number1"];
                $message .= "\r\nEmail Address: " . $_SESSION["email1"];
                mail($recipent, $subject, $message);
            } else {
                echo "Failed to request to leave club";
            }
        } elseif ($club_role == 1) {
            echo "Please request the administrator to reassign your club(s)";
        }
    } elseif ($action == "CancelLeave") {
        $cancel_leave_sql = "UPDATE users_clubs SET UsersClub_Approval='Approved' WHERE User_ID='$user_id' AND Club_ID='$club_id'";
        if ($link->query($cancel_leave_sql)) {
            echo "Canceled leave request";
        } else {
            echo "Failed to cancel leave request";
        }
    }
}
$link->close();
?>