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
    $members_sql =  "SELECT users.User_ID, users.User_FirstName, users.User_LastName, users.User_ContactNumber1, users.User_ContactNumber2, users.User_EmailAddress1, users.User_EmailAddress2
                    FROM users";
    if (isset($club_id)) {
        $members_sql .= " INNER JOIN users_clubs ON users.User_ID=users_clubs.User_ID
                        WHERE NOT users_clubs.UsersClub_Approval='Pending' AND users_clubs.UsersClub_Role>=2 AND users_clubs.Club_ID='$club_id'";
    } else if (isset($event_id)) {
        $members_sql .= " INNER JOIN users_events ON users.User_ID=users_events.User_ID
                        WHERE NOT users_events.UsersEvent_Approval='Pending' AND (users_events.UsersEvent_Role BETWEEN 2 AND 4) AND users_events.Event_ID='$event_id'";
    }
    $members_sql .= "ORDER BY users.User_FirstName ASC";
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
            <p>MEMBER DETAILS LIST</p>
            <p><?php if (isset($club_name)) {echo strtoupper($club_name);} elseif (isset($event_name)) {echo strtoupper($event_name);} ?></p>
        </div>
    
        <div class="content">
            <div class="section-title">MEMBERS</div>
            <table>
                <thead>
                    <tr>
                        <th width="12.5%">TP NO.</th>
                        <th width="17.5%">NAME</th>
                        <th width="15%">CONTACT NUMBER 1</th>
                        <th width="15%">CONTACT NUMBER 2</th>
                        <th width="20%">EMAIL ADDRESS 1</th>
                        <th width="20%">EMAIL ADDRESS 2</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($member = $members->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $member["User_ID"] ?></td>
                        <td><?php echo $member["User_FirstName"] . " " . $member["User_LastName"] ?></td>
                        <td><?php echo $member["User_ContactNumber1"] ?></td>
                        <td><?php echo $member["User_ContactNumber2"] ?></td>
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
}?>