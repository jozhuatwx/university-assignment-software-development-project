<?php
include_once("../server.php");

if (isLoggedIn()) {
    $link->query("UPDATE users SET User_LastOnline=CURRENT_TIMESTAMP() WHERE User_ID='$user_id'");
}
?>