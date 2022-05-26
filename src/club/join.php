<!-- Join Club -->
<?php
@include_once("../server.php");
@include_once("../phpscript/club.php");

// Check if requested
$join_request_sql = "SELECT User_ID FROM users_clubs WHERE UsersClub_Approval='Pending' AND User_ID='$user_id' AND Club_ID='$club_id'";
$join_request = $link->query($join_request_sql)->fetch_assoc();

if ($join_request["User_ID"]) { ?>
<div style="text-align: center; margin: 100px">
<h5 style="margin-bottom: 20px">Your request is being reviewed.</h5>
    <a class="waves-effect waves-light btn" onclick="requestClubEvent('CancelJoin', '<?php echo $club_id ?>', null)"><i class="material-icons left">cancel</i>Cancel Request</a>
</div>
<?php } else { ?>
<div style="text-align: center; margin: 100px">
    <h5 style="margin-bottom: 20px">You are not a member of <?php echo $club["Club_Name"] ?>.</h5>
    <?php if (isAdvisor()) { ?>
    <p style="margin-bottom: 20px">Note: To be an advisor for this club, please request the administrator to reassign your club(s).</p>
    <?php } ?>
    <a class="waves-effect waves-light btn <?php if (isAdvisor() || isAdmin()) { echo "disabled"; } ?>" onclick="requestClubEvent('Join', '<?php echo $club_id ?>', null)"><i class="material-icons left">how_to_reg</i>Request to Join</a>
</div>
<?php } 
// Retrieve Public Club Announcements
$announcements_sql =    "SELECT *
                        FROM announcements
                        WHERE Club_ID='$club_id'
                        AND Announcement_Public = 1
                        ORDER BY Announcement_DateTime DESC";
$announcements = $link->query($announcements_sql);

if ($announcements->num_rows > 0) { ?>
<span class="section-title">Announcements</span>
<?php
while ($announcement = $announcements->fetch_assoc()) { ?>
<div class="row">
    <div class="col s12">
        <div class="card blue-grey darken-1">
            <div class="card-content white-text">
                <span class="card-title"><?php echo $announcement["Announcement_Title"] ?></span>
                <p><?php echo nl2br($announcement["Announcement_Statement"]) ?></p>
            </div>
        </div>
    </div>
</div>
<?php }
} ?>