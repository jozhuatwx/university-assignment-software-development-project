<?php
@include_once("../server.php");

if (isLoggedIn()) {
    if (isset($_POST["search"])) {
        $search = $link->real_escape_string($_POST["search"]);
        $search .= "%";

        // Retrieve Clubs
        $prepared_clubs = $link->prepare("SELECT * FROM clubs WHERE Club_Type=? AND Club_Name LIKE ? ORDER BY Club_Name ASC");
        $prepared_clubs->bind_param("ss", $club_type, $search);
    } else {
        // Retrieve Clubs
        $prepared_clubs = $link->prepare("SELECT * FROM clubs WHERE Club_Type=? ORDER BY Club_Name ASC");
        $prepared_clubs->bind_param("s", $club_type);
    }

    // Types of clubs
    $club_types = array(
        "Sports" => "Sports & Recreation",
        "Society" => "Societies & Special Interest Groups",
        "Community" => "International Communities"
    );

    foreach ($club_types as $club_type => $description) { ?>
        <div class="row">
            <span class="col section-title"><?php echo $description ?></span>
        </div>

        <div class="row">
            <?php
            $has_result = false;
            $prepared_clubs->execute();

            $clubs = $prepared_clubs->get_result();
            while ($club = $clubs->fetch_assoc()) {
                $has_result = true;
                $club_starttime = date("h:i A", strtotime($club["Club_StartTime"]));
                $club_endtime = date("h:i A", strtotime($club["Club_EndTime"]));
            ?>
            <div class="col xl3 l4 m6 s12">
                <div class="card sticky-action blue-grey darken-1 waves-effect waves-light">
                    <div class="card-image activator">
                        <img src="resource/images/clublogo/<?php echo $club['Club_Logo'] ?>">
                    </div>
                    <div class="card-content activator">
                        <span class="card-title grey-text text-lighten-4"><?php echo $club["Club_Name"] ?><i class="material-icons right">more_vert</i></span>
                    </div>
                    <div class="card-reveal">
                        <span class="card-title grey-text text-darken-4"><?php echo $club["Club_Name"] ?><i class="material-icons right">close</i></span>
                        <p>Days: <?php echo $club["Club_Day"] ?></p>
                        <p>Time: <?php echo $club_starttime . " - " . $club_endtime; ?></p>
                        <p>Location: <?php echo $club["Club_Location"] ?></p>
                    </div>
                    <div class="card-action blue-grey darken-2">
                        <a onclick="openPage('club.php', '<?php echo $club['Club_ID'] ?>');">View Club</a>
                    </div>
                </div>
            </div>
        <?php }
        if ($has_result == false) { ?>
            <div class="col s12" style="text-align: center; margin: 15px 0">No clubs</div>
        <?php } ?>
        </div>
    <?php }
    $prepared_clubs->close();
}
$link->close();
?>