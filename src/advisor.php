<?php
include_once("server.php");
if (!isLoggedIn()) {
    header("Location: login.php");
}
if (!isAdmin()) {
    header("Location: nopermission.php");
}
?>
<style>
.select-wrapper .caret {
    fill: white;
}

.select-wrapper input.select-dropdown {
    color: white;
}
</style>

<?php // Retrieve Advisors
$advisors_sql = "SELECT users.User_ID, users.User_FirstName, users.User_LastName
                FROM users
                INNER JOIN users_clubs
                ON users.User_ID = users_clubs.User_ID
                WHERE users_clubs.UsersClub_Role = 1
                GROUP BY User_ID
                ORDER BY User_FirstName ASC";

$advisors = $link->query($advisors_sql);

// Retrieve Clubs
$clubs_sql = "SELECT Club_ID, Club_Name
            FROM clubs
            ORDER BY Club_Name";

$clubs = $link->query($clubs_sql);
$club_array = array();
while ($row = $clubs->fetch_assoc()) {
    $club_array[] = $row;
}

// Prepare Individual Advisors Club
$prepared_advisors_club = $link->prepare("SELECT Club_ID FROM users_clubs WHERE User_ID=?");
$prepared_advisors_club->bind_param("s", $user_id);
?>

<!-- Assign Advisor -->
<div class="row" style="margin: 15px 20px">
    <div class="col xl8 offset-xl2 l10 offset-l1 m10 offset-m1 s12">
        <span class="section-title">Assign Advisor</span>
        <div class="card-panel blue-grey darken-1 white-text">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Assigned Club</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($advisor = $advisors->fetch_assoc()) {
                        $user_id = strtoupper($advisor["User_ID"]);
                        $prepared_advisors_club->execute();
                        $advisors_club_result = $prepared_advisors_club->get_result();

                        $advisors_club = array();
                        while ($row = $advisors_club_result->fetch_assoc()) {
                            $advisors_club[] = $row["Club_ID"];
                        };
                        ?>
                    <tr>
                        <td><?php echo $user_id; ?></td>
                        <td><?php echo $advisor["User_FirstName"] . " " . $advisor["User_LastName"]; ?></td>
                        <td>
                            <div class="input-field" style="margin: 0" onchange="updateAdvisor('<?php echo $user_id; ?>', this)">
                                <select multiple>
                                    <?php foreach ($club_array as $club) { ?>
                                    <option <?php if (in_array($club["Club_ID"], $advisors_club)) { echo "selected"; } ?>><?php echo $club["Club_Name"]; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>