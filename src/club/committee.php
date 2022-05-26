<?php
include_once("../server.php");
include_once("../phpscript/club.php");

if ($club_role <= 3 || isAdmin()) {
?>
<style>
.card-panel {
    padding: 10px;
}

.card-image img.profile-pic {
    max-height: 175px;
    max-width: 175px;
    margin: 15px auto 0 auto;
    object-fit: contain;
    border-radius: 50%;
}

.committee.card-panel {
    padding: 15px 15px 5px 15px;
}

.committee img {
    margin: 7.5px 0;
}

.committee label {
    top: 0;
}
</style>

<!-- Committee Members -->
<div class="row">
    <div class="col s12">
        <span class="section-title">Committee Members</span>
        <?php if ($club_role <= 2 || isAdmin()) { ?>
        <a href="report/committeelist.php?club_id=<?php echo $club_id ?>" target="_blank" class="waves-effect waves-dark btn-flat right"><i class="material-icons left">print</i>Print List</a>
        <?php } ?>
    </div>
</div>
<?php
// Retrieve Members
$members_sql =  "SELECT users.User_ID, users.User_FirstName, users.User_LastName, users.User_Picture, users_clubs.UsersClub_CommitteeRole, usersclub_committeeroles.UsersClub_CommitteeRoleDetails
                FROM users
                INNER JOIN users_clubs ON users.User_ID=users_clubs.User_ID
                LEFT JOIN usersclub_committeeroles ON users_clubs.UsersClub_CommitteeRole=usersclub_committeeroles.UsersClub_CommitteeRole
                WHERE users_clubs.Club_ID='$club_id' AND users_clubs.UsersClub_Role>=2
                ORDER BY usersclub_committeeroles.UsersClub_CommitteeRole ASC";
$members = $link->query($members_sql);
$members_array = array();
while ($row = $members->fetch_assoc()) {
    $members_array[] = $row;
}

if ($club_role == 1) {
    $committee_roles_sql = "SELECT * FROM usersclub_committeeroles";
    $committee_roles = $link->query($committee_roles_sql); ?>
        <?php while ($committee_role = $committee_roles->fetch_assoc()) { ?>
            <div class="committee card-panel white">
            <div class="row">
            <?php
            $exist = false;
            foreach ($members_array as $member) {
                if ($committee_role["UsersClub_CommitteeRole"] == $member["UsersClub_CommitteeRole"]) { ?>
                    <img src="resource/images/profilepicture/<?php echo $member["User_Picture"]; ?>" alt="" class="col s1 circle">
            <?php
                $exist = true;
                }
            }
            if (!$exist) { ?>
                <div class="col s1"></div>
            <?php } ?>
            <div class="col s11">
                <div class="input-field" onchange="updateCommittee('<?php echo $committee_role['UsersClub_CommitteeRole'] ?>', '<?php echo $club_id ?>', this)">
                    <select id="club<?php echo $committee_role["UsersClub_CommitteeRole"]; ?>">
                        <option selected disabled>Select committee member</option>
                        <?php
                        foreach ($members_array as $member) { ?>
                            <option value="<?php echo $member["User_ID"] ?>" <?php if ($committee_role["UsersClub_CommitteeRole"] == $member["UsersClub_CommitteeRole"]) { echo "selected"; } ?>><?php echo $member["User_FirstName"] . " " . $member["User_LastName"] . " (" . $member["User_ID"] . ")"; ?></option>
                        <?php } ?>
                    </select>
                    <label class="active" for="club<?php echo $committee_role["UsersClub_CommitteeRole"] ?>"><?php echo $committee_role["UsersClub_CommitteeRoleDetails"] ?></label>
                </div>
            </div>
        </div>
        </div>
        <?php } ?>
<!-- <div class="card-panel white">
    <ul class="collection">
        <?php while ($committee_role = $committee_roles->fetch_assoc()) { ?>
        <li class="collection-item avatar" style="margin-top: 5px">
            <?php
            foreach ($members_array as $member) {
                if ($committee_role["UsersClub_CommitteeRole"] == $member["UsersClub_CommitteeRole"]) { ?>
                    <img src="resource/images/profilepicture/<?php echo $member["User_Picture"]; ?>" alt="" class="circle">
            <?php }
            } ?>
            <div class="row">
                <div class="input-field" style="margin: 10px 0 0 15px" onchange="updateCommittee('<?php echo $committee_role['UsersClub_CommitteeRole'] ?>', '<?php echo $club_id ?>', this)">
                    <select id="club<?php echo $committee_role["UsersClub_CommitteeRole"]; ?>">
                        <option selected disabled>Select committee member</option>
                        <?php
                        foreach ($members_array as $member) { ?>
                            <option value="<?php echo $member["User_ID"] ?>" <?php if ($committee_role["UsersClub_CommitteeRole"] == $member["UsersClub_CommitteeRole"]) { echo "selected"; } ?>><?php echo $member["User_FirstName"] . " " . $member["User_LastName"] . " (" . $member["User_ID"] . ")"; ?></option>
                        <?php } ?>
                    </select>
                    <label class="active" for="club<?php echo $committee_role["UsersClub_CommitteeRole"] ?>"><?php echo $committee_role["UsersClub_CommitteeRoleDetails"] ?></label>
                </div>
            </div>
        </li>
        <?php } ?>
    </ul>
</div> -->
<?php
} else {
    $committee_members_sql =    "SELECT users.User_ID, users.User_FirstName, users.User_LastName, users.User_ContactNumber1, users.User_EmailAddress1, users.User_Picture, users_clubs.UsersClub_CommitteeRole, usersclub_committeeroles.UsersClub_CommitteeRoleDetails
                                FROM users
                                INNER JOIN users_clubs ON users.User_ID=users_clubs.User_ID
                                INNER JOIN usersclub_committeeroles ON users_clubs.UsersClub_CommitteeRole=usersclub_committeeroles.UsersClub_CommitteeRole
                                WHERE users_clubs.Club_ID='$club_id' AND users_clubs.UsersClub_Role=2
                                ORDER BY usersclub_committeeroles.UsersClub_CommitteeRole ASC";
    $committee_members = $link->query($committee_members_sql);
?>
<div class="row">
<?php while ($committee_member = $committee_members->fetch_assoc()) { ?>
    <div class="col l4 m6 s12">
        <div class="card sticky-action blue-grey darken-1">
            <div class="card-image waves-effect waves-block waves-light activator">
                <img class="profile-pic activator" src="resource/images/profilepicture/<?php echo $committee_member["User_Picture"]; ?>">
            </div>
            <div class="card-content white-text">
                <span class="card-title activator"><?php echo $committee_member["User_FirstName"] . " " . $committee_member["User_LastName"] ?><i class="material-icons right">more_vert</i></span>
                <p><?php echo $committee_member["UsersClub_CommitteeRoleDetails"] ?></p>
            </div>
            <div class="card-reveal">
                <span class="card-title grey-text text-darken-4"><?php echo $committee_member["User_FirstName"] . " " . $committee_member["User_LastName"] ?><i class="material-icons right">close</i></span>
                <p><?php echo $committee_member["User_ContactNumber1"] ?></p>
                <p><?php echo $committee_member["User_EmailAddress1"] ?></p>
            </div>
        </div>
    </div>
<?php } ?>
</div>
<?php }
} else {
    include_once("../nopermission.php");
}
$link->close()
?>