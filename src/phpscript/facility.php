<?php
include_once("../server.php");

if (isLoggedIn()) {
    if (isset($_POST["action"])) {
        $action = $_POST["action"];
    }

    if (isset($_POST["club_id"])) {
        $club_id = "'" . $link->real_escape_string($_POST["club_id"]) . "'";
        
        $clubs_sql =    "SELECT clubs.Club_Name, users_clubs.UsersClub_Role
                        FROM users_clubs
                        INNER JOIN clubs ON users_clubs.Club_ID=clubs.Club_ID
                        WHERE users_clubs.User_ID='$user_id'
                        AND clubs.Club_ID=$club_id";

        if ($clubs = $link->query($clubs_sql)) {
            $clubs = $clubs->fetch_assoc();
            $club_role = $clubs["UsersClub_Role"];
            $club_name = $clubs["Club_Name"];
        } else {
            echo "Error retrieving Club Role";
            $error = true;
        }
    } else {
        $club_id = "NULL";
    }
    if (isset($_POST["event_id"])) {
        $event_id = "'" . $link->real_escape_string($_POST["event_id"]) . "'";
        
        $events_sql =    "SELECT events.Event_Name, users_events.UsersEvent_Role
                        FROM users_events
                        INNER JOIN events ON users_events.Event_ID=events.Event_ID
                        WHERE users_events.User_ID='$user_id'
                        AND events.Event_ID=$event_id";

        if ($events = $link->query($events_sql)) {
            $events = $events->fetch_assoc();
            $event_role = $events["UsersEvent_Role"];
            $event_name = $events["Event_Name"];
        } else {
            echo "Error retrieving Event Role";
            $error = true;
        }
    } else {
        $event_id = "NULL";
    }

    if (isset($_POST["start_datetime"])) {
        $start_datetime = date("Y-m-d H:i:s", strtotime($link->real_escape_string($_POST["start_datetime"])));
    }
    if (isset($_POST["end_datetime"])) {
        $end_datetime = date("Y-m-d H:i:s", strtotime($link->real_escape_string($_POST["end_datetime"])));
    }
    if (isset($_POST["description"])) {
        $description = $link->real_escape_string($_POST["description"]);
    }
    if (isset($_POST["attendees"])) {
        $attendees = $link->real_escape_string($_POST["attendees"]);
    }
    if (isset($_POST["request_id"])) {
        $request_id = $link->real_escape_string($_POST["request_id"]);
    }

    // Validation
    if ($action == "RoomRequest" || $action == "UpdateRoomRequest" || $action == "TransportationRequest" || $action == "UpdateTransportationRequest") {
        if (empty($description)) {
            echo "Description cannot be empty\n";
            $error = true;
        }
        if (empty($start_datetime)) {
            echo "Please select start/departure date and time\n";
            $error = true;
        }
        if (empty($end_datetime)) {
            echo "Please select end/return date and time\n";
            $error = true;
        }
        if (empty($attendees)) {
            echo "Number of attendees cannot be empty\n";
            $error = true;
        }

        if ($club_id == "NULL" && $event_id == "NULL") {
            echo "Error retrieving Club/Event ID\n";
            $error = true;
        }

        if (strlen($description) > 65535) {
            echo "Description cannot exceed 65535 characters\n";
            $error = true;
        }
        if (strtotime($start_datetime) <= date("Y-m-d", strtotime("+1 week"))) {
            echo "Start/ departure date must be a week away\n";
            $error = true;
        }
        if (strtotime($start_datetime) >= strtotime($end_datetime)) {
            echo "End/ return date cannot be equal or lesser than start/ departure date\n";
            $error = true;
        }
        if (!is_numeric($attendees)) {
            echo "Number of attendees must be be in numeric\n";
            $error = true;
        } elseif ($attendees < 3 || $attendees > 999) {
            echo "Number of attendees cannot be less than 3 or more than 999\n";
            $error = true;
        }

        // Retrieve admin emails
        $admin_emails_sql = "SELECT users.User_EmailAddress1
                            FROM users
                            INNER JOIN users_clubs ON users.User_ID=users_clubs.User_ID
                            WHERE users_clubs.UsersClub_Role=0";
        $admin_emails = $link->query($admin_emails_sql);

        $admin_recipent = "";
        // Email
        while ($admin_email = $admin_emails->fetch_assoc()) {
            $admin_recipent .= $admin_email["User_EmailAddress1"] . ", ";
        }
    }
    
    if ($action == "RoomRequest" || $action == "UpdateRoomRequest") {
        if (isset($_POST["room_name"]) && !empty($_POST["room_name"])) {
            $room_name = $link->real_escape_string($_POST["room_name"]);
        } else {
            echo "Please select a room\n";
            $error = true;
        }
    } elseif ($action == "TransportationRequest" || $action == "UpdateTransportationRequest") {
        if (isset($_POST["destination"]) && !empty($_POST["destination"])) {
            $destination = $link->real_escape_string($_POST["destination"]);
        } else {
            echo "Destination cannot be empty\n";
            $error = true;
        }
        if (isset($_POST["transportation_type"]) && !empty($_POST["transportation_type"]) && strlen($_POST["transportation_type"]) <= 16) {
            $transportation_type = $link->real_escape_string($_POST["transportation_type"]);
        } else {
            echo "Please select transportation type\n";
            $error = true;
        }
        if (isset($_POST["departure_site"]) && !empty($_POST["departure_site"]) && strlen($_POST["departure_site"]) <= 61) {
            $departure_site = $link->real_escape_string($_POST["departure_site"]);
        } else {
            echo "Please select departure site(s)\n";
            $error = true;
        }
        if (isset($_POST["return_site"]) && !empty($_POST["return_site"]) && strlen($_POST["return_site"]) <= 61) {
            $return_site = $link->real_escape_string($_POST["return_site"]);   
        } else {
            echo "Please select return site(s)\n";
            $error = true;
        }

        if (strlen($destination) > 65535) {
            echo "Destination cannot exceed 65535 characters\n";
            $error = true;
        }
    }

    if ($action == "AvailableRooms") {
        if (isset($_POST["rooms_request_id"])) {
            $rooms_request_id = $link->real_escape_string($_POST["rooms_request_id"]);
        }
        $available_rooms_sql =  "SELECT Room_ID, Room_Name
                                FROM rooms
                                WHERE NOT Room_ID IN (
                                    SELECT Room_ID FROM rooms_requests
                                    WHERE (('$start_datetime' >= RoomsRequest_StartDateTime AND '$start_datetime' < RoomsRequest_EndDateTime)
                                    OR ('$end_datetime' > RoomsRequest_StartDateTime AND '$end_datetime' <= RoomsRequest_EndDateTime)
                                    OR ('$start_datetime' <= RoomsRequest_StartDateTime AND '$end_datetime' >= RoomsRequest_EndDateTime))
                                    AND NOT RoomsRequest_Approval='Rejected'
                                    AND NOT RoomsRequest_ID='$rooms_request_id'
                                )";
        $available_rooms = $link->query($available_rooms_sql);
        
        while ($available_room = $available_rooms->fetch_assoc()) {
            echo "<option value='" . $available_room['Room_ID'] . "'>" . $available_room["Room_Name"] . "</option>";
        }
    } elseif (($action == "RoomRequest" || $action == "UpdateRoomRequest") && !$error && ((isset($club_role) && $club_role == 2) || (isset($event_role) && ($event_role == 2 || $event_role == 3))) && !isAdmin()) {
        if ($action == "RoomRequest") {
            $request_room_id_sql = "SELECT MAX(RoomsRequest_ID) FROM rooms_requests";
            $request_room_id = $link->query($request_room_id_sql)->fetch_assoc();

            if ($request_room_id["MAX(RoomsRequest_ID)"]) {
                $request_room_id = "RR" . str_pad(trim($request_room_id["MAX(RoomsRequest_ID)"], "RR") + 1, 6, 0, STR_PAD_LEFT);
            } else {
                $request_room_id = "RR000000";
            }

            $request_room_sql = "INSERT INTO rooms_requests
                                VALUES ('$request_room_id', '$description', '$start_datetime', '$end_datetime', '$attendees', 'Pending', '$user_id', $club_id, $event_id, (SELECT Room_ID FROM rooms WHERE Room_Name='$room_name'))";
            if ($link->query($request_room_sql)) {
                echo "Room requested";

                // Email
                $subject = "Room Request";
                if (isset($club_name)) {
                    $message = $club_name;
                } elseif (isset($event_name)) {
                    $message = $event_name;
                }
                $message .= " has request room " . $room_name . " from " . $start_datetime . " till " . $end_datetime;
                $message .= "\r\nPerson-in-charge: " . $_SESSION["first_name"] . " " . $_SESSION["last_name"] . " (" . $user_id . ")";
                $message .= "\r\nContact Number: " . $_SESSION["contact_number1"];
                $message .= "\r\nEmail Address: " . $_SESSION["email1"];
                mail($admin_recipent, $subject, $message, $mail_headers);
            } else {
                echo "Failed to request room";
            }
        } elseif ($action == "UpdateRoomRequest") {
            $update_room_request_sql =  "UPDATE rooms_requests
                                        SET RoomsRequest_Description='$description', RoomsRequest_StartDateTime='$start_datetime', RoomsRequest_EndDateTime='$end_datetime', RoomsRequest_NumberofAttendees='$attendees', Room_ID=(SELECT Room_ID FROM rooms WHERE Room_Name='$room_name')
                                        WHERE RoomsRequest_ID='$request_id'";
            if ($link->query($update_room_request_sql)) {
                echo "Room request updated";
            } else {
                echo "Failed to update room request";
            }
        }
    } elseif ($action == "CancelRoomRequest" && ((isset($club_role) && $club_role == 2) || (isset($event_role) && ($event_role == 2 || $event_role == 3))) && !isAdmin()) {
        $delete_room_request_sql = "DELETE FROM rooms_requests WHERE RoomsRequest_ID='$request_id'";
        if ($link->query($delete_room_request_sql)) {
            echo "Room request canceled";
        } else {
            echo "Failed to cancel Room Request";
        }
    } elseif (($action == "TransportationRequest" || $action == "UpdateTransportationRequest") && !$error && ((isset($club_role) && $club_role == 2) || (isset($event_role) && ($event_role == 2 || $event_role == 3))) && !isAdmin()) {
        if ($action == "TransportationRequest") {
            $request_transport_id_sql = "SELECT MAX(TransportationsRequest_ID) FROM transportations_requests";
            $request_transport_id = $link->query($request_transport_id_sql)->fetch_assoc();

            if ($request_transport_id["MAX(TransportationsRequest_ID)"]) {
                $request_transport_id = "TR" . str_pad(trim($request_transport_id["MAX(TransportationsRequest_ID)"], "TR") + 1, 6, 0, STR_PAD_LEFT);
            } else {
                $request_transport_id = "TR000000";
            }   
            
            $request_transport_sql =    "INSERT INTO transportations_requests
                                        VALUES ('$request_transport_id', '$description', '$start_datetime', '$end_datetime', '$attendees', '$destination', '$departure_site', '$return_site', 'Pending', '$user_id', $club_id, $event_id, (SELECT Transportation_ID FROM transportations WHERE Transportation_Type='$transportation_type'))";
            if ($link->query($request_transport_sql)) {
                echo "Transportation requested";

                // Email
                $subject = "Transportation Request";
                if (isset($club_name)) {
                    $message = $club_name;
                } elseif (isset($event_name)) {
                    $message = $event_name;
                }
                $message .= " has request transportation  " . $transportation_type . " for " . $start_datetime . " (departure) and " . $end_datetime . " (return)";
                $message .= "\r\nPerson-in-charge: " . $_SESSION["first_name"] . " " . $_SESSION["last_name"] . " (" . $user_id . ")";
                $message .= "\r\nContact Number: " . $_SESSION["contact_number1"];
                $message .= "\r\nEmail Address: " . $_SESSION["email1"];
                mail($admin_recipent, $subject, $message, $mail_headers);
            } else {
                echo "Failed to request transportation";
            }
        } elseif ($action == "UpdateTransportationRequest") {
            $update_transportation_request_sql =    "UPDATE transportations_requests
                                                    SET TransportationsRequest_Description='$description', TransportationsRequest_DepartureDateTime='$start_datetime', TransportationsRequest_ReturnDateTime='$end_datetime', TransportationsRequest_NumberofAttendees='$attendees', TransportationsRequest_Destination='$destination', TransportationsRequest_DepartureSite='$departure_site', TransportationsRequest_ReturnSite='$return_site', Transportation_ID=(SELECT Transportation_ID FROM transportations WHERE Transportation_Type='$transportation_type')
                                                    WHERE TransportationsRequest_ID='$request_id'";
            if ($link->query($update_transportation_request_sql)) {
                echo "Transportation request updated";
            } else {
                echo "Failed to update transportation request";
            }
        }
    } elseif ($action == "CancelTransportationRequest" && ((isset($club_role) && $club_role == 2) || (isset($event_role) && ($event_role == 2 || $event_role == 3))) && !isAdmin()) {
        $delete_transportation_request_sql = "DELETE FROM transportations_requests WHERE TransportationsRequest_ID='$request_id'";
        if ($link->query($delete_transportation_request_sql)) {
            echo "Transportation request canceled";
        } else {
            echo "Failed to cancel transportation request";
        }
    } elseif ($action == "Approve" && isAdmin()) {
        if ($request_id[0] == "R") {
            $update_room_request_sql = "UPDATE rooms_requests SET RoomsRequest_Approval='Approved' WHERE RoomsRequest_ID='$request_id'";
            if ($link->query($update_room_request_sql)) {
                echo "Approved room request";

                $student_emails = $link->query("SELECT User_EmailAddress1 FROM users WHERE User_ID=(SELECT User_ID FROM rooms_requests WHERE RoomsRequest_ID='$request_id')")->fetch_assoc();
                $student_email = $student_emails["User_EmailAddress1"];

                $subject = "Room Request Approved";
                $message = "Your room request has been approved";
                mail($student_email, $subject, $message);
            } else {
                echo "Failed to approve room request";
            }
        } elseif ($request_id[0] == "T") {
            $update_transportation_request_sql = "UPDATE transportations_requests SET TransportationsRequest_Approval='Approved' WHERE TransportationsRequest_ID='$request_id'";        
            if ($link->query($update_transportation_request_sql)) {
                echo "Approved transportation request";

                $student_emails = $link->query("SELECT User_EmailAddress1 FROM users WHERE User_ID=(SELECT User_ID FROM transportations_requests WHERE TransportationsRequest_ID='$request_id')")->fetch_assoc();
                $student_email = $student_emails["User_EmailAddress1"];

                $subject = "Transportation Request Approved";
                $message = "Your transportation request has been approved";
                mail($student_email, $subject, $message);
            } else {
                echo "Failed to approve transportation request";
            }
        }
    } elseif ($action == "Reject" && isAdmin()) {
        if ($request_id[0] == "R") {
            $update_room_request_sql = "UPDATE rooms_requests SET RoomsRequest_Approval='Rejected' WHERE RoomsRequest_ID='$request_id'";
            if ($link->query($update_room_request_sql)) {
                echo "Rejected room request";

                $student_emails = $link->query("SELECT User_EmailAddress1 FROM users WHERE User_ID=(SELECT User_ID FROM rooms_requests WHERE RoomsRequest_ID='$request_id')")->fetch_assoc();
                $student_email = $student_emails["User_EmailAddress1"];

                $subject = "Room Request Rejected";
                $message = "Your room request has been rejected";
                mail($student_email, $subject, $message);
            } else {
                echo "Failed to reject room request";
            }
        } elseif ($request_id[0] == "T") {
            $update_transportation_request_sql = "UPDATE transportations_requests SET TransportationsRequest_Approval='Rejected' WHERE TransportationsRequest_ID='$request_id'";        
            if ($link->query($update_transportation_request_sql)) {
                echo "Rejected Transportation Request";

                $student_emails = $link->query("SELECT User_EmailAddress1 FROM users WHERE User_ID=(SELECT User_ID FROM transportations_requests WHERE TransportationsRequest_ID='$request_id')")->fetch_assoc();
                $student_email = $student_emails["User_EmailAddress1"];

                $subject = "Transportation Request Rejected";
                $message = "Your transportation request has been rejected";
                mail($student_email, $subject, $message);
            } else {
                echo "Failed to reject transportation request";
            }
        }
    }
}
$link->close();
?>