<?php
include_once("../server.php");

if (isset($_POST["login"])) {
    if (isset($_POST["user_id"])) {
        $user_id = $link->real_escape_string($_POST["user_id"]);

        if (strlen($user_id) < 3) {
            echo "User ID cannot be less than 3 characters";
            $error = true;
        }
    } else {
        echo "User ID cannot be empty";
        $error = true;
    }
    if (isset($_POST["password"])) {
        $password = $link->real_escape_string($_POST["password"]);

        if (strlen($password) < 5) {
            echo "Password cannot be less than 5 characters";
            $error = true;
        }
    } else {
        header("Location: ../login.php");
        $error = true;
    }

    if (!$error) {
        $user_password_sql = "SELECT User_Password FROM users WHERE User_ID = '$user_id'";
        if ($user_password = $link->query($user_password_sql)) {
            $user_password = $user_password->fetch_assoc();
            if (password_verify($password, $user_password["User_Password"])) {
                $user_sql =     "SELECT User_FirstName, User_LastName, User_EmailAddress1, User_EmailAddress2, User_ContactNumber1, User_Picture
                                FROM users
                                WHERE User_ID='$user_id'";
                $user_role_sql =    "SELECT UsersClub_Role
                                    FROM users_clubs
                                    WHERE User_ID='$user_id'";
                if ($user = $link->query($user_sql)->fetch_assoc()) {
                    $_SESSION["user_id"] = strtoupper($user_id);
                    $_SESSION["first_name"] = $user["User_FirstName"];
                    $_SESSION["last_name"] = $user["User_LastName"];
                    $_SESSION["email1"] = $user["User_EmailAddress1"];
                    $_SESSION["email2"] = $user["User_EmailAddress2"];
                    $_SESSION["contact_number1"] = $user["User_ContactNumber1"];
                    $_SESSION["profile_picture"] = $user["User_Picture"];

                    $_SESSION["role"] = 4;
                    $user_roles = $link->query($user_role_sql);
                    while ($user_role = $user_roles->fetch_assoc()) {
                        if ($_SESSION["role"] > $user_role["UsersClub_Role"]) {
                            $_SESSION["role"] = $user_role["UsersClub_Role"];
                        }
                    }
                    unset($_SESSION["try_times"]);
                    echo "Login Successful";
                } else {
                    // Server error
                    echo "Server Error";
                }
            } else {
                // Wrong Password
                echo "Wrong User ID and/or Password";

                if (isset($_SESSION["try_times"])) {
                    $_SESSION["try_times"] += 1;

                    if ($_SESSION["try_times"] >= 3) {
                        $_SESSION["last_try"] = date("Y-m-d H:i:s", strtotime("+10 minutes"));
                        echo " Blocked";
                    }
                } else {
                    $_SESSION["try_times"] = 1;
                }
            }
        } else {
            // Wrong User ID
            echo "Wrong User ID and/or Password";

            if (isset($_SESSION["try_times"])) {
                $_SESSION["try_times"] += 1;

                if ($_SESSION["try_times"] >= 3) {
                    $_SESSION["last_try"] = date("Y-m-d H:i:s", strtotime("+10 minutes"));
                    echo " Blocked";
                }
            } else {
                $_SESSION["try_times"] = 1;
            }
        }
    }
}

/*
$first_names = array("Nur", "Muhammad", "Sarah", "Daniel");
$last_names = array("Siti", "Admad", "Lee", "");
$emails = array();
$nos = array(0,2,3,4,5,6,7,8,9);

$i = 0;
$p = $link->prepare("INSERT INTO users VALUES (?,?,?,?,?,?,NULL,?,NULL,?,NULL)");
$p->bind_param("ssssssss", $id, $pw, $fn, $ln, $dob, $no, $email, $pic);
foreach ($first_names as $fn) {
    $id = "TP04" . mt_rand(4,7) . str_pad(mt_rand(0, 999), 3, "0", STR_PAD_LEFT);
    $pw = password_hash("student", PASSWORD_DEFAULT);
    $ln = $last_names[$i];
    $dob = "199" . mt_rand(0,9) . "-" . mt_rand(1,12) . "-" . mt_rand(1,28);
    $no = "01" . $nos[mt_rand(0,8)] . "-" . str_pad(mt_rand(0, 9999999), 7, "0", STR_PAD_LEFT);
    $pic = $user_id . ".jpg";
    $email = $emails[$i];
    $p->execute();
    $i++;
}
*/
$link->close();
?>