<?php
include_once("server.php");
if (!isLoggedIn()) {
    header("Location: login.php");
}
?>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
.nopermission.block {
    text-align: center;
    height: 100vh;
    padding: calc(50vh - 175px) 20px;
    background-color: lightcyan;
    margin: 0;
    font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
}

.nopermission.block h5 {
    font-size: 1.64rem;
    line-height: 150%;
    font-weight: 400;
}

.nopermission .material-icons {
    font-size: 15rem;
    margin-bottom: 30px;
}

</style>
<div class="row nopermission block">
    <i class="material-icons col s12 blue-grey-text">vpn_lock</i>
    <h5 class="col s12">You do not have permission to view this information.</h5>
</div>