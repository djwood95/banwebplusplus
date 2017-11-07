var count = 0;
var calList = [];
function addCourseToCalendar(crn, courseNum) {
	$.get('/public/getCourseInfoForCalendar/' + crn + "/" + courseNum, function(responseTxt){
		console.log(crn);
		console.log(responseTxt);
		CRNList.push(crn);
		console.log(calList);

		var data = responseTxt[0];
		var days = data.Days.split(""); //puts days list into an array
		var timeTxt = data.SectionTime;
		var time = data.SectionTime.split("-"); //splits the time into an array with start and end time
		var startTime = time[0].split(" ")[0];
		var startTimeAP = time[0].split(" ")[1];
		var startTimeH = parseInt(startTime.split(":")[0], 10);
		var startTimeM = parseInt(startTime.split(":")[1], 10);
		var roundedStartTime = roundTime(startTimeH, startTimeM, startTimeAP);
			startTimeH = roundedStartTime.timeH;
			startTimeM = roundedStartTime.timeM;
			startTimeAP = roundedStartTime.timeAP;

		var year = data.Year;
		var date = data.Dates.split("\/");
		var startMonth = date[0];
		var startDate = date[1].split("-")[0];
		var endDate = startDate[1];
		var endMonth = date[2];
		var course = data.CourseNum;
		
		var endTime = time[1].split(" ")[0];
		var endTimeAP = time[1].split(" ")[1];
		var endTimeH = endTime.split(":")[0];
		var endTimeM = endTime.split(":")[1];
		var roundedEndTime = roundTime(endTimeH, endTimeM, endTimeAP);
			endTimeH = roundedEndTime.timeH;
			endTimeM = roundedEndTime.timeM;
			endTimeAP = roundedEndTime.timeAP;

		console.log(startTimeH+":"+startTimeM + " - " + endTimeH+":"+endTimeM);

		//THIS IS TESTING FOR GENERATING THE ICS FILE!
		/*
		var str = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//hacksw/handcal//NONSGML v1.0//EN\n";
		//for(var i = 0; i < CRNList.length; i++) {
			var actualStartH = 0;
			if(startTimeAP == "pm") {
				actualStartH = parseInt(startTimeH + 12);
			}
			var actualEndH = 0;
			if(endTimeAP == "pm") {
				actualEndH = parseInt(endTimeH + 12);
			}
			str = str + "BEGIN:VEVENT\n";
			str = str + "UID:" + "uid1@example.com\n"; //NEED TO GRAB EMAIL
			str = str + "DTSTAMP:" + year + startMonth + startDate + "T" + actualStartH + startTimeM + "00Z\n";
			str = str + "ORGANIZER;CN=John Doe:MAILTO:john.doe@example.com\n" //need to grab name and email here too
			str = str + "DTSTART:" + year + startMonth + startDate + "T" + actualStartH + startTimeM + "00Z\n";
			str = str + "DTEND:" + year + endMonth + endMonth + "T" + actualEndH + endTimeM + "00Z\n";
			str = str + "SUMMARY:" + course + "\n";
			str = str + "END:VEVENT\n";
		//}
		str = str + "END:VCALENDAR";
		calList.push(str);
		console.log(calList);
		console.log(str);
		*/

		var timeList = ['0600am', '0630am', '0700am', '0730am', '0800am', '0830am', '0900am', '0930am', '1000am', '1030am', 
                        '1100am', '1130am', '1200pm', '1230pm', '0100pm', '0130pm', '0200pm', '0230pm', '0300pm', '0330pm', 
                        '0400pm', '0430pm', '0500pm', '0530pm', '0600pm', '0630pm', '0700pm', '0730pm', '0800pm', '0830pm', 
                        '0900pm', '0930pm', '1000pm', '1030pm'];
		var timeH = startTimeH;
		var timeM = startTimeM;
		var timeAP = startTimeAP;
		var time = startTimeH + startTimeM + startTimeAP;
		var endTime = endTimeH + endTimeM + endTimeAP;
		var timeIndex = timeList.indexOf(time);
		var endTimeIndex = timeList.indexOf(endTime);
		console.log("endTimeIndex = " + endTime);
		var count = 0;

		while(timeIndex < endTimeIndex) {
			time = timeList[timeIndex];
			console.log(timeIndex + " | " + time);


			$.each(days, function(i, day){
				$('.courseFiller.'+day+'-'+time).addClass('full');
				if(count == 0) {
					$('.courseFiller.'+day+'-'+time).html(courseNum + "<br/>" + timeTxt);
					$('.courseFiller.'+day+'-'+time).addClass('first');

					if(timeIndex % 2 == 1){
						var prevTime = timeList[timeIndex - 1];
						$('.courseFiller.'+day+'-'+prevTime).addClass('borderBottom');
					}
				}

				if(count != 0 && timeIndex % 2 == 0) {
					var prevTime = timeList[timeIndex - 2];
					$('td.'+day+'-'+time).addClass('greenBorderTop');
					$('td.'+day+'-'+prevTime).addClass('greenBorderBottom');
				}

				if((timeIndex + 1) == endTimeIndex) {
					//If ends on a 30min interval, make sure it has black border
					if(endTimeIndex % 2 == 1) {
						$('.courseFiller.'+day+'-'+time).addClass('borderBottom');
					}
				}
			});

			count++;
			timeIndex++;
		}
	});
}

function roundTime(timeH, timeM, timeAP) {
	//round start time to half hour
	if(timeM >= 15 && timeM <= 45){
		timeM = "30";

	//round to this hour
	} else if(timeM < 15) {
		timeM = "00";

	//round to next hour
	} else if(timeM > 45) {
		timeM = "00";
		timeH++;
		timeAP = (timeH == 12 ? "pm" : timeAP); //switch to pm if h went 11->12
	}

	//pad hours with 0 so length is always 2
	timeH = ("0" + timeH).slice(-2);



	return {'timeH': timeH, 'timeM': timeM, 'timeAP': timeAP};
}