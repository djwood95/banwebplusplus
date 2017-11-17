var count = 0;
var calList = [];
var courseList = []; //an array of course objects that have been added to the calendar
var creditCount = 0;
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
			var startTimeH24_padded = (`0` + startTimeH24).slice(-2);
			var startTimeM = parseInt(startTime.split(`:`)[1], 10);
			var startTimeM_padded = (`0` + startTimeM).slice(-2);
			var startTimeMin = startTimeH24 * 60 + startTimeM;
			var startTime24H = startTimeH24_padded+":"+startTimeM_padded+":00";

			var endTime = time[1].split(` `)[0];
			var endTimeAP = time[1].split(` `)[1];
			var endTimeH = parseInt(endTime.split(`:`)[0]);
			var endTimeH24 = convertTo24Hr(endTimeH, endTimeAP);
			var endTimeH24_padded = (`0` + endTimeH24).slice(-2);
			var endTimeM = parseInt(endTime.split(`:`)[1]);
			var endTimeM_padded = (`0` + endTimeM).slice(-2);
			var endTimeMin = endTimeH24 * 60 + endTimeM;
			var endTime24H = endTimeH24_padded+":"+endTimeM_padded+":00";

			var dates = data.Dates.split('-');
			var year = data.Year;
			var startDate = dates[0].split('/');
				var startDateMonth = startDate[0];
				var startDateDay = startDate[1];
				startDate = year+"-"+startDateMonth+"-"+startDateDay;
			var endDate = dates[1].split('/');
				var endDateMonth = endDate[0];
				var endDateDay = endDate[1];
				endDate = year+"-"+endDateMonth+"-"+endDateDay;

			var course = data.CourseNum;
			
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

		var courseNoSpaces = course.replace(" ", "");
		$.each(days, function(i, day){
			var startOffset = (startTimeM / 60) * 100;
			var durationOffset = (duration / 60) * 100;
			var newHTML = `<div class='calClass course-`+courseNoSpaces+` crn-`+crn+`'
							style='top:`+startOffset+`%;height:`+durationOffset+`%;'
							data-courseNum='`+course+`' data-section='`+section+`'
							data-toggle='modal' data-target='.courseInfoBox' data-coursenum='`+courseNum+`'
						   >
						   		`+course+` `+section+`<br/>`+timeTxt+`
						   </div>`;
			$('td.'+day+'-'+startTimeH+'00'+startTimeAP+'>.tdWrapper').append(newHTML);
		});

		var Course = {
			'crn': crn,
			'courseNum': courseNum,
			'courseName': data.CourseName,
			'location': data.Location,
			'startTime': startTime24H,
			'endTime': endTime24H,
			'startDate': startDate,
			'endDate': endDate,
			'startTimeMin': startTimeMin,
			'duration': duration,
			'days': days,
			'credits': data.Credits
		}

		//Add to "Added Courses" list
		newHtml = `<li class='list-group-item classListItem course-`+courseNoSpaces+` crn-`+data.CRN+`' 
					data-courseNum='`+course+`' data-section='`+section+`'>`;
			newHtml += `<p data-courseNum='`+course+`' data-section='`+section+`' data-toggle='modal' data-target='.courseInfoBox' data-coursenum='`+courseNum+`'>`;
			newHtml += course+": "+data.CourseName+" "+data.SectionNum+"</p>";
			newHtml += data.Credits + " Credits";
			newHtml += `<a href='#' class='badge badge-danger ml-1 removeBtn float-right'
							data-crn='`+data.CRN+`' data-credits='`+data.Credits+`' data-toggle='tooltip' title='Remove this section from your calendar' data-placement='top'>REMOVE</a>`;
		newHtml += "</li>";

		$('#coursesAddedList').append(newHtml);

		courseList.push(Course);

		creditCount += parseInt(data.Credits);
		$('#creditCount').text(creditCount);
		$('#classCount').text(courseList.length);

		updateCalEventListeners();

	});
}

function updateCalEventListeners() {
	$('.calClass, .classListItem').hover(
		function() {
			var courseNum = $(this).data('coursenum').replace(" ", "");
			$('.course-'+courseNum).addClass("hover");
		},

		function() {
			$('.calClass').removeClass("hover");
			$('.classListItem').removeClass("hover");
		}
	);

	$('.removeBtn').unbind('click.namespace').bind('click.namespace', function() {
		var crn = $(this).data('crn');
		var credits = $(this).data('credits');
		removeCourse(crn, credits);
	});

}

function infoBoxEventListeners() {
	/*
	$('.removeBtn').unbind('click.namespace').bind('click.namespace', function() {
		var crn = $(this).data('crn');
		var credits = $(this).data('credits');
		removeCourse(crn);
	});
	*/
}

function removeCourse(crn, credits) {
	credits = parseInt(credits);
	var indexes = $.map(courseList, function(Course, index) {
	    if(Course.crn == crn) {
	        return index;
	    }
	});
	var i = indexes[0];

	courseList.splice(i, 1); //remove course from courseList
	$('.calClass.crn-'+crn).remove();
	$('.classListItem.crn-'+crn).remove();
	$('.courseInfoBox').modal('hide');

	creditCount = creditCount - credits;
	classCount = courseList.length;
	$('#creditCount').text(creditCount);
	$('#classCount').text(classCount);
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