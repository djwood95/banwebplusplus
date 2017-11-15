var count = 0;
var calList = [];
var courseList = []; //an array of course objects that have been added to the calendar
function addCourseToCalendar(crn, courseNum) {
	$.get('/public/getCourseInfoForCalendar/' + crn + `/` + courseNum, function(responseTxt){

		try {
			var data = responseTxt[0];
			var days = data.Days.split(``); //puts days list into an array
			var timeTxt = data.SectionTime;
			var section = data.SectionNum;
			var time = data.SectionTime.split(`-`); //splits the time into an array with start and end time
			var startTime = time[0].split(` `)[0];
			var startTimeAP = time[0].split(` `)[1];
			var startTimeH = parseInt(startTime.split(`:`)[0], 10);
			var startTimeH24 = convertTo24Hr(startTimeH, startTimeAP);
			var startTimeM = parseInt(startTime.split(`:`)[1], 10);
			var startTimeMin = startTimeH24 * 60 + startTimeM;

			var date = data.Dates.split(`\/`);
			var startMonth = date[0];
			var startDate = date[1].split(`-`)[0];
			var endDate = startDate[1];
			var endMonth = date[2];
			var course = data.CourseNum;
			
			var endTime = time[1].split(` `)[0];
			var endTimeAP = time[1].split(` `)[1];
			var endTimeH = parseInt(endTime.split(`:`)[0]);
			var endTimeH24 = convertTo24Hr(endTimeH, endTimeAP);
			var endTimeM = parseInt(endTime.split(`:`)[1]);
			var endTimeMin = endTimeH24 * 60 + endTimeM;
		} catch(e) {
			showDangerAlert(`Sorry, the course you tried to add does not have a valid time, and can't be added to your calendar.`);
			console.log(e);
			return;
		}

		var duration = endTimeMin - startTimeMin;

		//check if startTime div can be found - show error if not and exit.
		startTimeH = (`0` + startTimeH).slice(-2); //pad with leading 0
		if($('td.'+days[0]+'-'+startTimeH+'00'+startTimeAP).length == 0){
			showDangerAlert(`Sorry, the course you tried to add does not have a valid time, and can't be added to your calendar.`);
			return;
		} else if (duration < 1) {
			showDangerAlert(`Sorry, the course you tried to add does not have a valid time, and can't be added to your calendar.`);
			return;
		} else if (hasTimeConflicts(days, startTimeMin, endTimeMin)) {
			showDangerAlert(`Sorry, the course you tried to add conflicts with another course on your calendar, and can't be added to your calendar.`);
			return;
		}

		$.each(days, function(i, day){
			var startOffset = (startTimeM / 60) * 100;
			var durationOffset = (duration / 60) * 100;
			var courseNoSpaces = course.replace(" ", "");
			var newHTML = `<div class='calClass course-`+courseNoSpaces+` crn-`+crn+`'
							style='top:`+startOffset+`%;height:`+durationOffset+`%;'
							data-courseNum='`+course+`' data-section='`+section+`'
							data-toggle='modal' data-target='.courseInfoBox' data-coursenum='`+courseNum+`'
						   >
						   		`+course+` `+section+`<br/>`+timeTxt+`
						   </div>`;
			$('td.'+day+'-'+startTimeH+'00'+startTimeAP).append(newHTML);
		});

		var Course = {
			'crn': crn,
			'courseNum': courseNum,
			'startTimeMin': startTimeMin,
			'duration': duration,
			'days': days
		}

		courseList.push(Course);

		updateCalEventListeners();

	});
}

function updateCalEventListeners() {
	$('.calClass').hover(
		function() {
			//var courseNum = $(this).data('coursenum').replace(" ", "");
			//console.log($(this).data('coursenum'));
			//var section = $(this).data('section');
			//$('.calClass.'+courseNum+'-'+section).addClass("hover");
			//console.log('.calClass.'+courseNum+'-'+section);
			//$('.calClass').addClass("hover");
			var courseNum = $(this).data('coursenum').replace(" ", "");
			$('.calClass.course-'+courseNum).addClass("hover");
		},

		function() {
			$('.calClass').removeClass("hover");
		}
	);

}

function infoBoxEventListeners() {
	$('.removeBtn').click(function() {
		var crn = $(this).data('crn');
		removeCourse(crn);
	});
}

function removeCourse(crn) {
	var indexes = $.map(courseList, function(Course, index) {
	    if(Course.crn == crn) {
	        return index;
	    }
	});
	var i = indexes[0];

	courseList.splice(i, 1); //remove course from courseList
	$('.calClass.crn-'+crn).remove();
	$('.courseInfoBox').modal('hide');
}


function hasTimeConflicts(days, startTimeMin, endTimeMin) {

	var returnVal = false;
	$.each(days, function(i, day) {
		$.each(courseList, function(j, Course) {

			//if course's start time is between the start and end time of another course for the same day, time conflict = true
			if(startTimeMin >= Course.startTimeMin && startTimeMin <= (Course.startTimeMin + Course.duration) && Course.days.indexOf(day) != -1) {
				returnVal = true;
				return false;

			//If the course's end time is between the start and end time of another course for the same day, time conflict = true
			}else if(endTimeMin >= Course.startTimeMin && endTimeMin <= (Course.startTimeMin + Course.duration) && Course.days.indexOf(day) != -1) {
				returnVal = true;
				return false;
			}
		});
	});

	return returnVal;
}

function convertTo24Hr(hr, AP) {
	if(AP == `pm` && hr != 12){
		hr += 12;
	}

	return hr;
}

var alertTimer;
function showDangerAlert(text) {
	hideAllAlerts();
	$('.alert-danger').fadeIn();
	$('.alert-danger').text(text);

	$('.alert').click(function(){ hideAllAlerts(); });

	alertTimer = setTimeout(function() {
		hideAllAlerts();
	}, 5000);
}

function showGreenAlert(text) {
	hideAllAlerts();
	$('.alert-success').fadeIn();
	$('.alert-success').text(text);

	$('.alert').click(function(){ hideAllAlerts(); });

	alertTimer = setTimeout(function() {
		hideAllAlerts();
	}, 5000);
}

function hideAllAlerts() {
	$('.alert').fadeOut();
	clearTimeout(alertTimer);
}