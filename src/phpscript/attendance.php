<?php
include_once("../server.php");

if (isLoggedIn()) {
    if (isset($_POST["action"])) {
        $action = $_POST["action"];
    }

    if ($action == "TakeAttendance") {
        $presence = $link->real_escape_string($_POST["presence"]);
        switch ($presence) {
            case "Present":
                $presence = 1;
                break;
            default:
                $presence = 0;
                break;
        }
        if (isset($_POST["user_id"])) {
            $member_user_id = $link->real_escape_string($_POST["user_id"]);
        } else {
            echo "Failed to get member's User ID";
            $error = true;
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

            $event_role_sql =   "SELECT UsersEvent_Role
                                FROM users_events
                                WHERE User_ID='$user_id'
                                AND Event_ID=$event_id";

            $event_role = $link->query($event_role_sql)->fetch_assoc();
            $event_role = $event_role["UsersEvent_Role"];
        } else {
            $event_id = "NULL";
        }
        
        if (!$error && ($club_id != "NULL" || $event_id != "NULL")) {
            if ((isset($_POST["club_id"]) && $club_role == 2) || (isset($_POST["event_id"]) && ($event_role == 2 || $event_role == 3))) {
                $today = date("Y-m-d");
                $user_attendance = false;

                // Check If Total Exists
                $select_totalattendance_sql =   "SELECT total_attendances.TotalAttendance_ID
                                                FROM total_attendances
                                                INNER JOIN attendances ON total_attendances.TotalAttendance_ID=attendances.TotalAttendance_ID
                                                WHERE total_attendances.TotalAttendance_Date='$today'";
                if ($club_id != "NULL") {
                    $select_totalattendance_sql .= " AND attendances.Club_ID=$club_id";
                } elseif ($event_id != "NULL") {
                    $select_totalattendance_sql .= " AND attendances.Event_ID=$event_id";
                }
                
                if ($total_attendance_id = $link->query($select_totalattendance_sql)) {
                    $total_attendance_id = $total_attendance_id->fetch_assoc();
                    $total_attendance_id = $total_attendance_id["TotalAttendance_ID"];

                    // Check If User's Attendance Exists
                    $select_attendance_sql =    "SELECT attendances.Attendance_Presence
                                                FROM attendances
                                                INNER JOIN total_attendances ON attendances.TotalAttendance_ID=total_attendances.TotalAttendance_ID
                                                WHERE attendances.User_ID='$member_user_id' AND total_attendances.TotalAttendance_Date='$today'";
                    if ($club_id != "NULL") {
                        $select_attendance_sql .= " AND attendances.Club_ID=$club_id";
                    } elseif ($event_id != "NULL") {
                        $select_attendance_sql .= " AND attendances.Event_ID=$event_id";
                    }

                    $user_attendance_result = $link->query($select_attendance_sql);
                    if ($user_attendance_result->num_rows > 0) {
                        $user_attendance = true;
                    }
                }

                if (!$total_attendance_id) {
                    // Insert Into Total Attendance
                    $insert_totalattendance_sql =  "INSERT INTO total_attendances
                                                    VALUES (NULL, '$today', 0)";
                    $link->query($insert_totalattendance_sql);

                    $select_totalattendance_id_sql = "SELECT MAX(TotalAttendance_ID) AS Max_ID FROM total_attendances WHERE TotalAttendance_Date='$today' AND TotalAttendance_Quantity=0";
                    if ($total_attendance_id = $link->query($select_totalattendance_id_sql)->fetch_assoc()) {
                        $total_attendance_id = $total_attendance_id["Max_ID"];
                    }
                }

                if (!$user_attendance) {
                    // Insert Into Attendance
                    $insert_attendance_sql =    "INSERT INTO attendances
                                                VALUES (NULL, $presence, '$member_user_id', $club_id, $event_id, $total_attendance_id)";
                    if ($link->query($insert_attendance_sql)) {
                        if ($presence == 1) {
                            $message = "Present";
                        } else {
                            $message = "Absent";
                        }
                        echo $member_user_id . " " . $message;

                        // Update Total Attendance Quantity
                        if ($presence == 1) {
                            $update_totalattendance_sql =   "UPDATE total_attendances
                                                            SET TotalAttendance_Quantity=TotalAttendance_Quantity + 1
                                                            WHERE TotalAttendance_ID=$total_attendance_id";
                            $link->query($update_totalattendance_sql);
                        }
                    } else {
                        echo $insert_attendance_sql;
                    }
                } else {
                    // Update Attendance
                    $update_attendance_sql =    "UPDATE attendances
                                                SET Attendance_Presence=$presence
                                                WHERE User_ID='$member_user_id'
                                                AND TotalAttendance_ID=(SELECT TotalAttendance_ID FROM total_attendances WHERE TotalAttendance_Date='$today')";
                    if ($club_id != "NULL") {
                        $update_attendance_sql .= "AND Club_ID=$club_id";
                    } elseif ($event_id != "NULL") {
                        $update_attendance_sql .= "AND Event_ID=$event_id";
                    }
                    if ($link->query($update_attendance_sql)) {
                        if ($presence == 1) {
                            $message = "Present";
                        } else {
                            $message = "Absent";
                        }
                        echo $member_user_id . " " . $message;
                    }

                    // Update Total Attendance Quantity
                    $update_totalattendance_sql = "UPDATE total_attendances SET TotalAttendance_Quantity=TotalAttendance_Quantity ";
                    if ($presence == 1) {
                        $update_totalattendance_sql .= "+ 1";
                    } else {
                        $update_totalattendance_sql .= "- 1";
                    }
                    $update_totalattendance_sql .=  " WHERE TotalAttendance_ID=$total_attendance_id";
                    $link->query($update_totalattendance_sql);
                }
            } else {
                echo "Not permitted to take attendance";
            }
        }
    }
}
$link->close();
?>