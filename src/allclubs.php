<?php
include_once("server.php");
if (!isLoggedIn()) {
    header("Location: login.php");
}
?>

<div class="search" style="margin: 15px 20px">
    <!-- Search -->
    <div class="row">
        <div class="col xl4 l5 m6 s7">
            <div class="row">
                <div class="input-field col s12">
                    <i class="material-icons prefix">search</i>
                    <input type="text" id="search-input" class="autocomplete" onkeyup="searchClubs(this.value)">
                    <label for="search-input">Search</label>
                </div>
            </div>
        </div>
    </div>

    <div id="clubs-list" class="row">
        <?php include_once("phpscript/allclubs.php") ?>
    </div>
</div>