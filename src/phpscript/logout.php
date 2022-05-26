<?php
include_once("../server.php");

session_destroy();
header("Location: ../login.php");
?>