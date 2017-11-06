function addCourseToCalendar(crn, courseNum) {
	$.get('/public/getCourseInfoForCalendar/' + crn + "/" + courseNum, function(responseTxt){
		console.log(crn);
		console.log(responseTxt);
		CRNList.push(crn);

		var data = responseTxt[0];
		var days = data.Days.split(""); //puts days list into an array
		var timeTxt = data.SectionTime;
		var time = data.SectionTime.split("-"); //splits the time into an array with start and end time
		var startTime = time[0].split(" ")[0];
		var startTimeAP = time[0].split(" ")[1];
		var startTimeH = parseInt(startTime.split(":")[0], 10);
		var startTimeM = parseInt(startTime.split(":")[1], 10);
		var roundedStartTime = roundTime(startTimeH, startTimeM);
			startTimeH = roundedStartTime.timeH;
			startTimeM = roundedStartTime.timeM;
		
		var endTime = time[1].split(" ")[0];
		var endTimeAP = time[1].split(" ")[1];
		var endTimeH = endTime.split(":")[0];
		var endTimeM = endTime.split(":")[1];
		var roundedEndTime = roundTime(endTimeH, endTimeM);
			endTimeH = roundedEndTime.timeH;
			endTimeM = roundedEndTime.timeM;

		console.log(startTimeH+":"+startTimeM + " - " + endTimeH+":"+endTimeM);

		$.each(days, function(i, day){
			if(startTimeM == 0){
				$("." + day + "-" + startTimeH + startTimeAP).addClass('full');
				$("." + day + "-" + startTimeH + startTimeAP).html(courseNum + "<br/>" + timeTxt);
				$("." + day + "-" + startTimeH + startTimeAP).attr('data-target', '.courseInfoBox');
				$("." + day + "-" + startTimeH + startTimeAP).attr('data-toggle', 'modal');
				$("." + day + "-" + startTimeH + startTimeAP).attr('data-coursenum', courseNum);
			}else{
				$("." + day + "-" + startTimeH + startTimeAP).addClass('bottomHalf');
				var newStartTimeH = (startTimeH == 12 ? 1 : startTimeH + 1);
				$("." + day + "-" + newStartTimeH + startTimeAP).addClass('full');
				$("." + day + "-" + startTimeH + startTimeAP).html(courseNum + "<br/>" + timeTxt);
				$("." + day + "-" + startTimeH + startTimeAP).attr('data-target', '.courseInfoBox');
				$("." + day + "-" + startTimeH + startTimeAP).attr('data-toggle', 'modal');
				$("." + day + "-" + startTimeH + startTimeAP).attr('data-coursenum', courseNum);
			}
		});
	});
}

function roundTime(timeH, timeM) {
	//round start time to half hour
	if(timeM >= 15 && timeM <= 45){
		timeM = 30;

	//round to this hour
	} else if(timeM < 15) {
		timeM = 0;

	//round to next hour
	} else if(timeM > 45) {
		timeM = 0;
		timeH++;
	}

	return {'timeH': timeH, 'timeM': timeM};
}