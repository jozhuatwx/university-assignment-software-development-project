<?php
// MySQL Variables
$database_domain = "localhost";
$database_username = "root";
$database_password = "root";
$database_name = "sdp";

$link = new mysqli($database_domain, $database_username, $database_password, $database_name);

if ($link->connect_errno) {
    echo "Error: " . $link->connect_error();
    exit();
}

// Mail Variables
$server_email = "apucocurriculum@gmail.com";
$mail_headers = "From: " . $server_email . "\r\n";
?>