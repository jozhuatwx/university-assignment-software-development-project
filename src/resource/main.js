// Initialize components
M.AutoInit();

var modal_elems = document.querySelectorAll('.modal');
var modals = M.Modal.init(modal_elems, "");

var modal_title = document.getElementById("modal-title");
var modal_content = document.getElementById("modal-content");
var modal_button_confirm = document.getElementById("modal-button-confirm");

// Highlight sidenav button
// Get the container element
var sidenav = document.getElementById("slide-out");

// Get all buttons with class="btn" inside the container
var btns = sidenav.getElementsByTagName("li");

// Loop through the buttons and add the active class to the current/clicked button
for (var i = 1; i < btns.length; i++) {
    btns[i].addEventListener("click", function() {
        var current = sidenav.getElementsByClassName("active");

        // If there's no active class
        if (current.length > 0) {
            current[0].className = current[0].className.replace(" active", "");
        }

        // Add the active class to the current/clicked button
        this.className += " active";
    })
}

// XML HTTP Request (AJAX)
var xhttp = new XMLHttpRequest();

function loadXhttp(class_name, preload_html, file_name) {
    if (preload_html) {
        document.getElementsByClassName(class_name)[0].innerHTML = preload_html;
    }

    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementsByClassName(class_name)[0].innerHTML = this.responseText;
            M.AutoInit();

            var time_elems = document.querySelectorAll(".timepicker");
            M.Timepicker.init(time_elems, {twelveHour: false});

            var date_elems = document.querySelectorAll(".datepicker");
            M.Datepicker.init(date_elems, {format: "yyyy-mm-dd"});

            var modal_elems = document.querySelectorAll('.modal');
            modals = M.Modal.init(modal_elems, "");

            var graph = document.getElementById("graph");
            if (graph) {
                updateGraph();
            }
        } else if (this.readyState == 4 && this.status == 404)  {
            if (class_name == "maincontent") {
                openPage("notfound.php");
            } else if (class_name == "subcontent") {
                openSubpage("notfound.php", "Not Found");
            }
        }
    }

    xhttp.open("POST", file_name, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    return xhttp;
}

function updateNotification(action, club_id, event_id, element_id) {
    var notify = document.getElementById(element_id);

    if (notify) {
        var data = "action=" + action;
        if (club_id) {
            data += "&club_id=" + club_id;
        } else if (event_id) {
            data += "&event_id=" + event_id;
        }
        
        var xhttp = new XMLHttpRequest();

        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var total_notifcation = this.responseText;

                if (total_notifcation > 0) {
                    notify.innerHTML = total_notifcation;
                } else {
                    notify.parentNode.removeChild(notify);
                }
            }
        }

        xhttp.open("POST", "phpscript/notification.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send(data);
    }
}

function openPage(file_name, club_id, event_id, selected_month) {
    var preload_html = '<div class="main-preloader preloader-wrapper big active"><div class="spinner-layer spinner-green-only"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>';

    var xhttp = loadXhttp("maincontent", preload_html, file_name);
    var data = "";

    if (club_id) {
        data += "club_id=" + club_id;
    }
    if (event_id) {
        data += "event_id=" + event_id;
    }

    if (selected_month) {
        data += "&selected_month=" + selected_month;
    }

    xhttp.send(data);

    if (club_id) {
        var notify = document.getElementById("notify-" + club_id);
        if (notify) {
            updateNotification("main", club_id, null, "notify-" + club_id);
        }
    }
    if (event_id) {
        var notify = document.getElementById("notify-" + event_id);
        if (notify) {
            updateNotification("main", null, event_id, "notify-" + event_id);
        }
    }
}

function openSubpage(subfile_name, subpage_name, club_id, event_id, request_id, selected_month) {
    var preload_html = '<div class="sub-preloader preloader-wrapper big active"><div class="spinner-layer spinner-green-only"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>';
    if (subpage_name) {
        document.getElementById("sub-breadcrumb").innerHTML = subpage_name;
    }
    var xhttp = loadXhttp("subcontent", preload_html, subfile_name);
    var data = "";

    if (club_id) {
        data += "club_id=" + club_id;
        if (selected_month) {
            data += "&selected_month=" + selected_month;
        }
    } else if (event_id) {
        data += "event_id=" + event_id;
        if (selected_month) {
            data += "&selected_month=" + selected_month;
        }
    } else if (request_id) {
        data += "request_id=" + request_id;
    }
    xhttp.send(data);
}


function phpScripts(file_name, page_refresh, club_id, event_id) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            // Show toast notification
            if (this.responseText) {
                M.toast({html: this.responseText});
            }

            // Refresh page
            if (page_refresh) {
                if (club_id) {
                    openSubpage(page_refresh, null, club_id);
                } else if (event_id) {
                    openSubpage(page_refresh, null, null, event_id);
                } else {
                    openPage(page_refresh);
                }
            }

            if (this.responseText == "Update event pending approval" || this.responseText == "Cancel event pending approval") {
                if (this.responseText.startsWith("Update")) {
                    var button = document.getElementById("update-event");
                    var icon = button.getElementsByTagName("i")[0];
                    icon.classList.remove("light-blue");
                } else if (this.responseText.startsWith("Cancel")) {
                    var button = document.getElementById("cancel-event");
                    var icon = button.getElementsByTagName("i")[0];
                    icon.classList.remove("red");
                    icon.classList.remove("darken-2");
                }
                button.removeAttribute("onclick");
                button.onclick = function() {M.toast({html: "The request sent is pending approval"})};
                button.classList.add("grey-text");
                button.classList.add("text-darken-1");
                icon.classList.add("grey");
                icon.classList.add("darken-1");
            } else if (this.responseText == "Following event" || this.responseText == "Unfollowed event" || this.responseText == "Requested to volunteer event" || this.responseText == "Event request approved" || this.responseText == "Canceled volunteer request") {
                var my_events = document.getElementById("my-upcoming-events");
                var newXhttp = new XMLHttpRequest();
                newXhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        my_events.innerHTML = this.responseText;
                    }
                }
                newXhttp.open("POST", "phpscript/sidenavevent.php", true);
                newXhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                newXhttp.send();
            } else if (this.responseText == "You have been demoted to a regular committee") {
                openPage("event.php", null, event_id);
            }

            if (club_id) {
                updateNotification("member", club_id, null, "notify-member");
                updateNotification("main", club_id, null, "notify-" + club_id);
                updateNotification("event", club_id, null, "notify-event");
            } else if (event_id) {
                updateNotification("member", null, event_id, "notify-member");        
                updateNotification("main", null, event_id, "notify-" + event_id);
            }
            updateNotification("facility", null, null, "notify-facility");
        }
    }
    xhttp.open("POST", "phpscript/" + file_name, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    return xhttp;
}

function updateAdvisor(user_id, element) {
    var clubs = encodeURIComponent(element.getElementsByTagName("input")[0].value);
    var xhttp = phpScripts("advisor.php");

    xhttp.send("user_id=" + user_id + "&clubs=" + clubs);
}

function announcementConfirm(action, announcement_id, club_id, event_id, page) {
    modal_button_confirm.onclick = function () {announcement(action, announcement_id, club_id, event_id, page)};

    if (action == "Delete") {
        modal_title.innerHTML = "Delete Announcement"
        modal_content.innerHTML = "Do you confirm you want to delete this announcement?"
        modals[0].open();
    }
}

function announcement(action, announcement_id, club_id, event_id, page_name) {
    if (action == "Post") {
        var title = encodeURIComponent(document.getElementById("announcement-title").value);
        var statement = encodeURIComponent(document.getElementById("announcement-statement").value);
        var checkbox = document.getElementById("announcement-public");
        if (checkbox) {
            var public = checkbox.checked;
        }
    } else if (action == "Update") {
        var title = encodeURIComponent(document.getElementById("title-" + announcement_id).value);
        var statement = encodeURIComponent(document.getElementById("statement-" + announcement_id).value);
        var checkbox = document.getElementById("public-" + announcement_id);
        if (checkbox) {
            var public = checkbox.checked;
        }
    }
    if (public || (!club_id && !event_id)) {
        public_announcement = 1;
    } else {
        public_announcement = 0;
    }

    if (action == "Post" || action == "Update") {
        var data = "title=" + title + "&statement=" + statement + "&public_announcement=" + public_announcement + "&action=" + action;
        if (club_id) {
            data += "&club_id=" + club_id;
        }
        if (event_id) {
            data += "&event_id=" + event_id;
        }
        if (announcement_id) {
            data += "&announcement_id=" + announcement_id;
        }
    } else if (action == "Delete") {
        var data = "announcement_id=" + announcement_id + "&action=" + action;
        if (club_id) {
            data += "&club_id=" + club_id;
        }
        if (event_id) {
            data += "&event_id=" + event_id;
        }
    }

    if (page_name == "club") {
        var xhttp = phpScripts("announcement.php", "club/announcement.php", club_id);
    } else if (page_name == "event") {
        var xhttp = phpScripts("announcement.php", "event/announcement.php", null, event_id);
    } else {
        var xhttp = phpScripts("announcement.php", page_name);
    }

    xhttp.send(data);
}

function searchClubs(search) {
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("clubs-list").innerHTML = this.responseText;
        }
    }

    xhttp.open("POST", "phpscript/allclubs.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("search=" + encodeURIComponent(search));
}

function requestClubEvent(action, club_id, event_id) {
    if (action == "Join" || action == "CancelJoin") {
        page = "join.php";
    } else if (action == "Follow" || action == "Unfollow" || action == "Volunteer" || action == "CancelVolunteer") {
        page = "volunteer.php";
    } else if (action == "Leave" || action == "CancelLeave") {
        page = "leave.php";
    }

    var data = "action=" + action;

    if (club_id) {
        data += "&club_id=" + club_id;
        var xhttp = phpScripts("clubrequest.php", "club/" + page, club_id);
    } else if (event_id) {
        data += "&event_id=" + event_id;
        var xhttp = phpScripts("clubeventrequest.php", "event/" + page, null, event_id);
    }
    xhttp.send(data);
}

function updateLogo(club_id, event_id) {
    var file = document.getElementById("logo").files[0];
    var image_preview = document.getElementById("image-preview");
    if (club_id) {
        var navigation_icon = document.getElementById("navicon-" + club_id);
    } else if (event_id) {
        var navigation_icon = document.getElementById("navicon-" + event_id);
    }
    
    var formData = new FormData();
    formData.append("image", file);
    if (club_id) {
        formData.append("club_id", club_id);
    } else if (event_id) {
        formData.append("event_id", event_id);
    }
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            M.toast({html: this.responseText});
            if (this.responseText == "Logo updated") {
                if (club_id) {
                    src = "resource/images/clublogo/" + club_id + file.name.substr(-4, 4) + "?rnd=" + Math.random();
                    image_preview.src = src;
                    navigation_icon.src = src;
                } else if (event_id) {
                    src = "resource/images/eventlogo/" + event_id + file.name.substr(-4, 4) + "?rnd=" + Math.random();
                    image_preview.src = src;
                    navigation_icon.src = src;
                }
            }
        }
    }

    xhttp.open("POST", "phpscript/logo.php", true);
    xhttp.send(formData);
}

function updateClubDetails(action, club_id, element) {
    var value = encodeURIComponent(element.getElementsByTagName("input")[0].value);
    var data = "club_id=" + club_id;

    switch (action) {
        case "days":
            data += "&club_day=" + value;
            break;
        case "starttime":
            data += "&club_starttime=" + value;
            break;
        case "endtime":
            data += "&club_endtime=" + value;
            break;
        case "location":
            data += "&club_location=" + value;
            break;
    }

    var xhttp = phpScripts("clubdetails.php");
    xhttp.send(data);
}

function takeAttendance(element, user_id, club_id, event_id) {
    var presence = element.getElementsByTagName("input")[0].value;
    var xhttp = phpScripts("attendance.php");

    data = "action=TakeAttendance&presence=" + presence + "&user_id=" + user_id;
    if (club_id) {
        data += "&club_id=" + club_id;
    } else if (event_id) {
        data += "&event_id=" + event_id;
    }
    xhttp.send(data);
}

function selectedAttendanceMonth(element, club_id, event_id) {
    var selected_month = element.getElementsByTagName("input")[0].value;
    if (club_id) {
        openSubpage("club/attendance.php", "Attendance", club_id, null, null, selected_month);
    } else if (event_id) {
        openSubpage("event/attendance.php", "Attendance", null, event_id, null, selected_month);
    }
}

function attendanceReport(club_id, event_id) {
    var selected_month = document.getElementById("selected-month").getElementsByTagName("input")[0].value;
    
    var url = "report/attendancereport.php?"
    if (club_id) {
        url += "club_id=" + club_id;
    } else if (event_id) {
        url += "event_id=" + event_id;
    }
    url += "&selected_month=" + selected_month;
    var win = window.open(url, '_blank');
    win.focus();
}

function membershipConfirm(action, user_id, club_id, event_id) {
    modal_button_confirm.onclick = function () {membership(action, user_id, club_id, event_id)};

    if (action == "Approve") {
        modal_title.innerHTML = "Approve Request"
        modal_content.innerHTML = "Do you confirm you want to approve " + user_id + "'s request?"
        modals[0].open();
    } else if (action == "Reject") {
        modal_title.innerHTML = "Reject Request"
        modal_content.innerHTML = "Do you confirm you want to reject " + user_id + "'s request?"
        modals[0].open();
    } else if (action == "Sack") {
        modal_title.innerHTML = "Sack Member"
        modal_content.innerHTML = "Do you confirm you want to sack " + user_id + "?"
        modals[0].open();
    }
}

function membership(action, user_id, club_id, event_id) {
    if (club_id) {
        var xhttp = phpScripts("member.php", "club/member.php", club_id);
        xhttp.send("action=" + action + "&user_id=" + user_id + "&club_id=" + club_id);
    } else if (event_id) {
        var xhttp = phpScripts("member.php", "event/member.php", null, event_id);
        xhttp.send("action=" + action + "&user_id=" + user_id + "&event_id=" + event_id);
    }
}

function updateCommittee(committee_role, club_id, element) {
    var user = element.getElementsByTagName("input")[0].value;
    var user_id = user.substr(-9, 8);
    
    var xhttp = phpScripts("clubcommittee.php", "club/committee.php", club_id);
    xhttp.send("committee_role=" + committee_role + "&club_id=" + club_id + "&user_id=" + user_id);
}

function eventRequestConfirm(action, event_id, club_id) {
    modal_button_confirm.onclick = function () {eventRequest(action, event_id, club_id)};

    if (action == "Approve") {
        modal_title.innerHTML = "Approve Request";
        modal_content.innerHTML = "Do you confirm you want to approve this request?";
        modals[0].open();
    } else if (action == "Reject") {
        modal_title.innerHTML = "Reject Request";
        modal_content.innerHTML = "Do you confirm you want to reject this request?";
        modals[0].open();
    } else if (action == "UpdateEvent") {
        modal_title.innerHTML = "Update Event";
        modal_content.innerHTML = "Do you confirm you want to update this event? You may not submit another update request until this is approved/rejected";
        modals[0].open();
    } else if (action == "CancelEvent") {
        modal_title.innerHTML = "Cancel Event";
        modal_content.innerHTML = "Do you confirm you want to cancel this event?";
        modals[0].open();
    }
}

function eventRequest(action, event_id, club_id) {
    var data = "action=" + action;

    if (action == "CreateEvent" || action == "UpdateEvent") {
        var event_name = encodeURIComponent(document.getElementById("form-name").value);
        var description = encodeURIComponent(document.getElementById("form-description").value);
        var start_datetime = document.getElementById("form-startdate").value + " " + document.getElementById("form-starttime").value;
        var end_datetime = document.getElementById("form-enddate").value + " " + document.getElementById("form-endtime").value;
        var location = encodeURIComponent(document.getElementById("form-location").value);

        data += "&event_name=" + event_name + "&description=" + description + "&start_datetime=" + start_datetime + "&end_datetime=" + end_datetime + "&location=" + location;
        
        if (action == "CreateEvent") {
            var committee = document.getElementById("form-committee-wrapper").getElementsByClassName("select-wrapper")[0].getElementsByTagName("input")[0].value;
            data += "&committee=" + committee;
            var committeerole = encodeURIComponent(document.getElementById("form-committeerole").value);
            data += "&committeerole=" + committeerole;
        }
    }
    
    if (event_id) {
        data += "&event_id=" + event_id;
    }
    if (club_id) {
        data += "&club_id=" + club_id;
    }

    if (action == "CreateEvent") {
        var xhttp = phpScripts("eventrequest.php", "club/event.php", club_id);
    } else if (action == "Approve" || action == "Reject") {
        var xhttp = phpScripts("eventrequest.php", "club/event.php", club_id, event_id);
    } else if (action == "UpdateEvent" || action == "CancelEvent" || action == "LeaveEvent") {
        var xhttp = phpScripts("eventrequest.php", "event/announcement.php", null, event_id);
    }
    xhttp.send(data);
}

function updateEventCommittee(user_id, event_id) {
    var role = document.getElementById("role-" + user_id).value;
    var detail = document.getElementById("detail-" + user_id);

    if (role == "Committee") {
        detail.disabled = false;
    } else if (role == "Member") {
        detail.disabled = true;
        detail.value = "";
    }
    var xhttp = phpScripts("eventcommittee.php");
    xhttp.send("role=" + role + "&detail=" + encodeURIComponent(detail.value) + "&user_id=" + user_id + "&event_id=" + event_id);
}

function updateOrganiserConfirm(event_id) {
    modal_button_confirm.onclick = function () {updateOrganiser(event_id)};

    modal_title.innerHTML = "Change Organiser";
    modal_content.innerHTML = "Do you confirm you want to change the organiser? You will be immediately demoted to regular committee member.";
    modals[0].open();
}

function updateOrganiser(event_id) {
    var user_id = document.getElementById("new-organiser").value.substr(-9, 8);
    var xhttp = phpScripts("eventcommittee.php", "event/announcement.php", null, event_id);
    xhttp.send("user_id=" + user_id + "&event_id=" + event_id + "&role=Organiser&detail=Organiser");
}

function searchEvents(search, club_id) {
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("events-list").innerHTML = this.responseText;
        }
    }

    var data = "search=" + encodeURIComponent(search);
    if (club_id) {
        data += "&club_id=" + club_id;
    }

    xhttp.open("POST", "phpscript/allevents.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(data);
}

function availableRooms(rooms_request_id) {
    var start_datetime = document.getElementById("rform-startdate").value + " " + document.getElementById("rform-starttime").value;
    var end_datetime = document.getElementById("rform-enddate").value + " " + document.getElementById("rform-endtime").value;
    
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("rform-room").innerHTML = this.responseText;

            var elems = document.querySelectorAll('select');
            M.FormSelect.init(elems, "");
        }
    }

    xhttp.open("POST", "phpscript/facility.php", true);    
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    var data = "action=AvailableRooms&start_datetime=" + start_datetime + "&end_datetime=" + end_datetime;
    if (rooms_request_id) {
        data += "&rooms_request_id=" + rooms_request_id;
    }
    xhttp.send(data);
}

function roomRequest(action, request_id) {
    var rform = document.getElementById("rform");

    var name = rform.getElementsByClassName("select-wrapper")[0].getElementsByTagName("input")[0].value;
    var description = encodeURIComponent(document.getElementById("rform-description").value);
    var start_datetime = document.getElementById("rform-startdate").value + " " + document.getElementById("rform-starttime").value;
    var end_datetime = document.getElementById("rform-enddate").value + " " + document.getElementById("rform-endtime").value;
    var attendees = document.getElementById("rform-noattendees").value;
    var room_name = document.getElementById("rform-room-wrapper").getElementsByClassName("select-wrapper")[0].getElementsByTagName("input")[0].value;

    if (action == "UpdateRoomRequest") {
        var xhttp = phpScripts("facility.php");
    } else {
        var xhttp = phpScripts("facility.php", "facility.php");
    }

    var data = "action=" + action + "&description=" + description + "&start_datetime=" + start_datetime + "&end_datetime=" + end_datetime + "&attendees=" + attendees + "&room_name=" + room_name;
    if (name.substr(-7, 2) == "CB") {
        data += "&club_id=" + name.substr(-7, 6);
    } else if (name.substr(-11, 2) == "EV") {
        data += "&event_id=" + name.substr(-11, 10);
    }
    if (request_id) {
        data += "&request_id=" + request_id;
    }
    xhttp.send(data);
}

function facilityRequestConfirm(action, request_id) {
    modal_button_confirm.onclick = function () {facilityRequest(action, request_id)};

    if (action == "Approve") {
        modal_title.innerHTML = "Approve Request"
        modal_content.innerHTML = "Do you confirm you want to approve this request?"
        modals[0].open();
    } else if (action == "Reject") {
        modal_title.innerHTML = "Reject Request"
        modal_content.innerHTML = "Do you confirm you want to reject this request?"
        modals[0].open();
    }
}

function facilityRequest(action, request_id) {
    var xhttp = phpScripts("facility.php", "facility.php");
    xhttp.send("action=" + action + "&request_id=" + request_id);
}

function transportationRequest(action, request_id) {
    var tform = document.getElementById("tform");

    var name = tform.getElementsByClassName("select-wrapper")[0].getElementsByTagName("input")[0].value;
    var description = encodeURIComponent(document.getElementById("tform-description").value);
    var attendees = document.getElementById("tform-noattendees").value;
    var transportation_type = tform.getElementsByClassName("select-wrapper")[1].getElementsByTagName("input")[0].value;
    var destination = encodeURIComponent(document.getElementById("tform-destination").value);
    var start_datetime = document.getElementById("tform-startdate").value + " " + document.getElementById("tform-starttime").value;
    var departure_site = document.getElementById("tform-departuresite").getElementsByClassName("select-wrapper")[0].getElementsByTagName("input")[0].value;
    var end_datetime = document.getElementById("tform-enddate").value + " " + document.getElementById("tform-endtime").value;
    var return_site = document.getElementById("tform-returnsite").getElementsByClassName("select-wrapper")[0].getElementsByTagName("input")[0].value;

    if (action == "UpdateTransportationRequest") {
        var xhttp = phpScripts("facility.php");
    } else {
        var xhttp = phpScripts("facility.php", "facility.php");
    }

    var data = "action=" + action + "&description=" + description + "&attendees=" + attendees + "&destination=" + destination + "&transportation_type=" + transportation_type.substr(-4,3) + "&start_datetime=" + start_datetime + "&departure_site=" + departure_site + "&end_datetime=" + end_datetime + "&return_site=" + return_site;
    if (name.substr(-7, 2) == "CB") {
        data += "&club_id=" + name.substr(-7, 6);
    } else if (name.substr(-11, 2) == "EV") {
        data += "&event_id=" + name.substr(-11, 10);
    }
    if (request_id) {
        data += "&request_id=" + request_id;
    }
    xhttp.send(data);
}

function bookingReport() {
    var selected_month = "";

    var url = "report/approvedbookinglist.php?";
    if (selected_month) {
        url += "selected_month=" + selected_month;
    }
    var win = window.open(url, '_blank');
    win.focus();
}

function reportType() {
    var selected_report_type = document.getElementById("selected-report-type").getElementsByTagName("input")[0].value;
    var select_name = document.getElementById("selected-name");
    var selected_month = document.getElementById("selected-month");
    
    if (selected_report_type == "Overall Club Attendance" || selected_report_type == "Approved Booking") {
        select_name.style = "display: none";
        select_name.required = false;
    } else {
        select_name.style = "display: block";
        select_name.required = true;
    }
    if (selected_report_type == "Member" || selected_report_type == "Committee") {
        selected_month.style = "display: none";
        selected_month.required = false;
    } else {
        selected_month.style = "display: block";
        selected_month.required = true;
    }
}

function updateGraph() {
    var selected_report_type = document.getElementById("selected-report-type").getElementsByTagName("input")[0].value;
    var name = document.getElementById("selected-name").getElementsByTagName("input")[0].value;
    var selected_month = document.getElementById("selected-month").getElementsByTagName("input")[0].value;

    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("graph").innerHTML = this.responseText;
        }
    }

    data = "report_type=" + selected_report_type;
    if (selected_report_type != "Approved Booking" && selected_report_type != "Overall Club Attendance") {
        if (name.substr(-7, 2) == "CB") {
            data += "&club_id=" + name.substr(-7, 6);
        } else if (name.substr(-11, 2) == "EV") {
            data += "&event_id=" + name.substr(-11, 10);
        }
    }
    data += "&selected_month=" + selected_month;

    xhttp.open("POST", "phpscript/reportgraph.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(data);
}

function generateReport() {
    var selected_report_type = document.getElementById("selected-report-type").getElementsByTagName("input")[0].value;
    var name = document.getElementById("selected-name").getElementsByTagName("input")[0].value;
    var selected_month = document.getElementById("selected-month").getElementsByTagName("input")[0].value;

    var url = "report/";

    switch (selected_report_type) {
        case "Attendance":
            url += "attendancereport.php?";
            break;
        case "Overall Club Attendance":
            url += "overallclubattendancereport.php?";
            break;
        case "Member":
            url += "memberlist.php?";
            break;
        case "Committee":
            url += "committeelist.php?";
            break;
        case "Approved Booking":
            url += "approvedbookinglist.php?";
            break;
    }

    if (selected_report_type != "Approved Booking" && selected_report_type != "Overall Club Attendance") {
        if (name.substr(-7, 2) == "CB") {
            url += "&club_id=" + name.substr(-7, 6);
        } else if (name.substr(-11, 2) == "EV") {
            url += "&event_id=" + name.substr(-11, 10);
        }
    }

    if (selected_month) {
        url += "&selected_month=" + selected_month;
    }

    var win = window.open(url, '_blank');
    win.focus();    
}