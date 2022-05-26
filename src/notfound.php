<?php
include_once("server.php");
if (!isLoggedIn()) {
    header("Location: login.php");
}
?>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
.notfound.block {
    text-align: center;
    height: 100vh;
    padding: calc(50vh - 175px) 20px;
    background-color: lightcyan;
    margin: 0;
    font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
}

.notfound.block h5 {
    font-size: 1.64rem;
    line-height: 150%;
    font-weight: 400;
}

.notfound .material-icons {
    font-size: 15rem;
    margin-bottom: 30px;
}

</style>
<div class="row notfound block">
    <i class="material-icons col s12 blue-grey-text">sentiment_dissatisfied</i>
    <h5 class="col s12">Requested page not found.</h5>
</div>