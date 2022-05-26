<?php
include_once("../server.php");
include_once("../phpscript/club.php");

if (isCommittee()) {
    $members_sql = "SELECT users.User_ID, users.User_FirstName, users.User_LastName FROM users INNER JOIN users_clubs ON users.User_ID=users_clubs.User_ID WHERE users_clubs.UsersClub_Approval='Approved' AND (users_clubs.UsersClub_Role BETWEEN 2 AND 3) AND users_clubs.Club_ID='$club_id' AND NOT users_clubs.User_ID='$user_id' ORDER BY users.User_FirstName ASC";
    $members = $link->query($members_sql); ?>
<!-- Create Event -->
<span class="section-title">Create Event</span>
<div class="row">
    <div class="col s12">
        <div id="form" class="card white">
            <form onsubmit="event.preventDefault(); eventRequest('CreateEvent', null, '<?php echo $club_id ?>');">
                <div class="card-content black-text">
                    <span class="card-title">Create Event</span>
                    <div class="row">
                        <div class="input-field col m6 s12">
                            <input id="form-name" type="text" class="validate" maxlength="50" required>
                            <label for="form-name">Event Name</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <textarea id="form-description" class="materialize-textarea validate" data-length="65535" required></textarea>
                            <label for="form-description">Description</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col m6 s12">
                            <input id="form-startdate" type="date" class="datepicker no-autoinit validate" value="<?php echo date("Y-m-d", strtotime("+1 month")) ?>" min="<?php echo date("Y-m-d", strtotime("+1 month")) ?>" required>
                            <label for="form-startdate" class="active">Start Date</label>
                        </div>
                        <div class="input-field col m6 s12">
                            <input id="form-enddate" type="date" class="datepicker no-autoinit validate" value="<?php echo date("Y-m-d", strtotime("+1 month")) ?>" min="<?php echo date("Y-m-d", strtotime("+1 month")) ?>" required>
                            <label for="form-enddate" class="active">End Date</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col m6 s12">
                            <input id="form-starttime" type="time" class="timepicker no-autoinit validate" required>
                            <label for="form-starttime" class="active">Start Time</label>
                        </div>
                        <div class="input-field col m6 s12">
                            <input id="form-endtime" type="time" class="timepicker no-autoinit validate" required>
                            <label for="form-endtime" class="active">End Time</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col m6 s12">
                            <input type="text" value="<?php echo $user_id ?>" disabled>
                            <label class="active">Organiser</label>
                        </div>
                    </div>

                    <div class="row">
                        <div id="form-committee-wrapper" class="input-field col s12">
                            <select multiple>
                                <?php while ($member = $members->fetch_assoc()) { ?>
                                <option><?php echo $member["User_FirstName"] . " " . $member["User_LastName"] . " (" . $member["User_ID"] . ")" ?></option>
                                <?php } ?>
                            </select>
                            <label>Select Committee</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <textarea id="form-committeerole" class="materialize-textarea validate" data-length="65535"></textarea>
                            <label for="form-committeerole">Committee Role</label>
                            <p class="grey-text">Use comma ',' to separate the roles. Please leave blank if there is no committee.</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <textarea id="form-location" class="materialize-textarea validate" data-length="65535" required></textarea>
                            <label for="form-location">Location</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <button class="waves-effect waves-light btn" type="submit">Create Event</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
} else {
    include_once("../nopermission.php");
}
?>