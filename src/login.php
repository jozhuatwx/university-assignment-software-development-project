<?php
include_once("server.php");
if (isLoggedIn()) {
    header("Location: main.php");
}
if (isset($_SESSION["last_try"])) {
    if (strtotime($_SESSION["last_try"]) < strtotime(date("Y-m-d H:i:s"))) {
        unset($_SESSION["try_times"]);
        unset($_SESSION["last_try"]);
    }
}
$disabled = false;
if (isset($_SESSION["try_times"])) {
    if ($_SESSION["try_times"] >= 3) {
        $disabled = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login | APU Co-curriculum</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link rel="stylesheet" type="text/css" media="screen" href="resource/main.css" />
</head>
<style>
body {
    background: url("resource/images/apu_aerial.jpg") no-repeat center center fixed;
    background-size: cover;
}
</style>
<body>
    <div class="row">
        <div class="col l4 offset-l4 m6 offset-m3 s8 offset-s2" style="margin-top: calc(50vh - 325px)">
            <div class="card">
                <div class="card-image">
                    <img src="https://utemplates.net/wp-content/uploads/2016/10/utemplates_free-material-design-background-for-you.jpg">
                    <span class="card-title">Login</span>
                </div>
                <form onsubmit="event.preventDefault(); <?php if (!$disabled) { echo 'login()'; } ?>">
                    <div class="card-content">
                        <div class="row">
                            <div class="input-field col s8">
                                <input id="user_id" name="user_id" type="text" class="validate" minlength="3" maxlength="8" required <?php if ($disabled) { echo "disabled"; } ?>>
                                <label for="user_id">User ID</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s8">
                                <input id="password" name="password" type="password" class="validate" minlength="5" maxlength="20" required <?php if ($disabled) { echo "disabled"; } ?>>
                                <label for="password">Password</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-action">
                        <div class="row">
                            <?php if (!$disabled) { ?>
                            <button type="submit" class="btn waves-effect waves-light" style="background-color: #26a69a; color: #fff;">Submit</button>
                            <?php } else { ?>
                            <p>You have entered wrong credentials multiple times. Please try again later.</p>
                            <?php } ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function login() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                switch (this.responseText) {
                    case "Login Successful":
                        window.location.replace("main.php");
                        break;

                    case "Wrong User ID and/or Password Blocked":
                        window.location.replace("login.php");
                        break;
                
                    default:
                        M.toast({html: this.responseText})
                        break;
                }
            }
        }

        xhttp.open("POST", "phpscript/login.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("user_id=" + document.getElementById("user_id").value + "&password=" + document.getElementById("password").value + "&login=1");
    }
    </script>
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>