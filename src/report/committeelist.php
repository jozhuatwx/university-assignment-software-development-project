<?php
include_once("../server.php");
if (!isLoggedIn()) {
    header("Location: ../login.php");
}

if (isset($_GET["club_id"])) {
    include_once("../phpscript/club.php");
} elseif (isset($_GET["event_id"])) {
    include_once("../phpscript/event.php");
}

if ((isset($club_role) && $club_role <= 2) || (isset($event_role) && $event_role <= 3) || isAdmin()) {
    $members_sql =  "SELECT users.User_ID, users.User_FirstName, users.User_LastName, users.User_ContactNumber1, users.User_EmailAddress1, users.User_EmailAddress2";
    if (isset($club_id)) {
        $members_sql .= ", UsersClub_CommitteeRoleDetails FROM users
                        INNER JOIN users_clubs ON users.User_ID=users_clubs.User_ID
                        INNER JOIN usersclub_committeeroles ON users_clubs.UsersClub_CommitteeRole=usersclub_committeeroles.UsersClub_CommitteeRole
                        WHERE users_clubs.Club_ID='$club_id' AND users_clubs.UsersClub_Role=2
                        ORDER BY usersclub_committeeroles.UsersClub_CommitteeRole ASC";
    } elseif (isset($event_id)) {
        $members_sql .= ", UsersEvent_CommitteeRole FROM users
                        INNER JOIN users_events ON users.User_ID=users_events.User_ID
                        WHERE users_events.Event_ID='$event_id' AND (UsersEvent_Role BETWEEN 2 AND 3)";
    }
    $members = $link->query($members_sql); ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>APU Co-curriculum</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <?php include_once("../resource/report.php"); ?>
    
        <div class="header">
            <p>ASIA PACIFIC UNIVERSITY</p>
            <p>COMMITTEE DETAILS LIST</p>
            <p><?php if (isset($club_name)) {echo strtoupper($club_name);} elseif (isset($event_name)) {echo strtoupper($event_name);} ?></p>
        </div>
    
        <div class="content">
            <div class="section-title">COMMITTEE MEMBERS</div>
            <table>
                <thead>
                    <tr>
                        <th width="12.5%">TP NO.</th>
                        <th width="17.5%">NAME</th>
                        <th width="15%">ROLE</th>
                        <th width="15%">CONTACT NUMBER</th>
                        <th width="20%">EMAIL ADDRESS 1</th>
                        <th width="20%">EMAIL ADDRESS 2</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($member = $members->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $member["User_ID"] ?></td>
                        <td><?php echo $member["User_FirstName"] . " " . $member["User_LastName"] ?></td>
                        <td><?php if (isset($member["UsersClub_CommitteeRoleDetails"])) {echo $member["UsersClub_CommitteeRoleDetails"];} elseif (isset($member["UsersEvent_CommitteeRole"])) {echo $member["UsersEvent_CommitteeRole"];} ?></td>
                        <td><?php echo $member["User_ContactNumber1"] ?></td>
                        <td><?php echo $member["User_EmailAddress1"] ?></td>
                        <td><?php echo $member["User_EmailAddress2"] ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <script>window.print()</script>
    </body>
    </html>
<?php
} else {
    header("Location: ../nopermission.php");
}
?>