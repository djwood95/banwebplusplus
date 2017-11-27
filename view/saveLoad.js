function saveAs() {
    var scheduleName = prompt("Enter Name to save schedule to:", "My Schedule");
    var VariableIGrabbed = $('#semester').val();
    var ArrayThing = VariableIGrabbed.split(' ');
    var Semester = ArrayThing[0];
    var Year = ArrayThing[1];
    var crnList = [];

    for(var i = 0; i < courseList.length; i++) {
        crnList[i] = courseList[i]['crn']; 
    }

    var crns = crnList.join();

    $.get('/public/saveScheduleAs/'+ scheduleName + "/" + Year + "/" + Semester + "/" + crns, function(responseTxt) {
        console.log("save as..." + responseTxt);
        currentScheduleId = responseTxt.id;
        $('#currentScheduleName').text("Currently Editing " + scheduleName);
    });
}

function saveCurrentSchedule() {
    if(currentScheduleId != null) {
        var crnList = [];
        for(var i = 0; i < courseList.length; i++) {
            crnList[i] = courseList[i]['crn']; 
        }
        var crns = crnList.join();

        $.get('/public/saveSchedule/'+currentScheduleId+'/'+crns, function(responseTxt) {
            console.log("Schedule Saved!" + responseTxt);
        });
    }
}

/**
 * Loads list of schedules that can be opened into dialog box
 */
function loadSchedulesList() {
    $.get('/public/getScheduleList', function(responseTxt) {
        var newHtml = "<h1>Select a Schedule to Open:</h1>";

        newHtml += "<div class='list-group'>"
        $.each(responseTxt, function(i, data) {
            newHtml += "<a href='#' class='list-group-item list-group-item-action' onclick=\"openSchedule('"+data.id+"', '"+data.ScheduleName+"')\">";
                newHtml += "<b>"+data.ScheduleName+"</b> "+data.Semester+" "+data.ScheduleYear;
            newHtml += "</a>";
        });
        newHtml += "</div>";

        $('.openScheduleBox>.modal-dialog>.modal-content').html(newHtml);
    });
}

function openSchedule(id, name) {
    clearCalendar();
    $.get('/public/openSchedule/'+id, function(responseTxt) {
        $.each(responseTxt, function(i, course) {
            addCourseToCalendar(course.CRN, course.CourseNum);
            console.log(course.CRN, course.CourseNum);
        });
        $('.openScheduleBox').modal('hide');
        currentScheduleId = id;
        $('#currentScheduleName').text("Currently Editing " + name);
        listenForSearchClickEvents();
    });
}

function clearCalendar(mode) {
    currentScheduleId = null;
    $('#currentScheduleName').text("");

    while(courseList.length > 0){
        course = courseList[0];
        removeCourse(course.crn, course.credits);
    };
}