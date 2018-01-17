/**
 * Listeners for when the page is first loaded
 */
$(document).ready(function(){

	$('#searchBox').on("input", function(){search();});
	getAvailableSemesters();

	$('.openScheduleBox').on('show.bs.modal', function(event){
		loadSchedulesList();
	});

});

/**
 * Check backend if user is signed in
 */
function signInSuccess(name) {
	//check backend
	$.get('/public/isLoggedIn', function(responseTxt) {
		if(responseTxt){
			var firstName = name.split(" ")[0];
			$('.signInDropdown').text("Welcome, " + firstName);
		}else{
			showDangerAlert("Sorry, there was a problem signing you in.");
			console.log(responseTxt);
		}
	});
}

/**
 * Get list of semesters available in database, add them to dropdown list
 */
function getAvailableSemesters() {
	$.get('/public/getAvailableSemesters', function(responseTxt) {
		var semesterList = responseTxt;
		$.each(semesterList, function (i, semesterName) {
		    $('#semester').append($('<option>', { 
		        value: semesterName,
		        text : semesterName 
		    }));
		});
	});
}

/**
 * Listeners for buttons in the search results
 * Triggered everytime search results are updated
 */
function listenForSearchClickEvents() {
	$('.courseInfoBox').on('show.bs.modal', function(event){
		var clicker = $(event.relatedTarget);
		var courseNum = clicker.data('coursenum');
		loadCourseInfo(courseNum);
	});

	$('.addCourseBtn').on('click', function(event){
		var crn = $(this).data('coursecrn');
		var courseNum = $(this).data('coursenum');
		addCourseToCalendar(crn, courseNum); //in calendar.js
	});
}

/* Building names and abbreviations List */
const buildingNames = [];
	buildingNames[1] = {"abbr": "Admin", "full": "Administration Building"};
	buildingNames[3] = {"abbr": "Lakeshore Center", "full": "Michigan Tech Lakeshore Center"};
	buildingNames[4] = {"abbr": "ROTC", "full": "ROTC Building"};
	buildingNames[5] = {"abbr": "Academic Offices", "full": "Academic Offices Building"};
	buildingNames[6] = {"abbr": "Annex", "full": "Annex Building"};
	buildingNames[7] = {"abbr": "EERC", "full": "Electrical Energy Resources Building"};
	buildingNames[8] = {"abbr": "DOW", "full": "DOW Environmental Sciences & Engineering Building"};
	buildingNames[9] = {"abbr": "Alumni House", "full": "Alumni House"};
	buildingNames[10] = {"abbr": "Rozsa", "full": "Rozsa Center for Performing Arts & Education"};
	buildingNames[11] = {"abbr": "Walker", "full": "Walker - Arts & Humanities"};
	buildingNames[12] = {"abbr": "M&M", "full": "Minerals & Materials Engineering Building"};
	buildingNames[13] = {"abbr": "Hamar House", "full": "Hamar House"};
	buildingNames[14] = {"abbr": "Dillman", "full": "Grover C. Dillman Hall"};
	buildingNames[15] = {"abbr": "Fisher", "full": "Fisher Hall"};
	buildingNames[16] = {"abbr": "Widmaier House", "full": "Widmaier House"};
	buildingNames[17] = {"abbr": "Library", "full": "J.R. Van Pelt Library"};
	buildingNames[18] = {"abbr": "Forestry", "full": "U.J. Noblet Forestry Building"};
	buildingNames[19] = {"abbr": "ChemSci", "full": "Chemical Sciences & Engineering Building"};
	buildingNames[20] = {"abbr": "MEEM", "full": "R.L. Smith (MEEM) Building"};
	buildingNames[21] = {"abbr": "Volatile Liquid & Gas Building", "full": "Volatile Liquid & Gas Building"};
	buildingNames[24] = {"abbr": "SDC", "full": "Student Development Complex"};
	buildingNames[25] = {"abbr": "Sherman Field Press Box", "full": "Sherman Field Press Box"};
	buildingNames[27] = {"abbr": "Ford Forestry Center", "full": "Ford Forestry Center"};
	buildingNames[28] = {"abbr": "Rekhi", "full": "Kanwal and Ann Rekhi Hall"};
	buildingNames[31] = {"abbr": "DHH", "full": "Douglas Houghton Hall"};
	buildingNames[34] = {"abbr": "MUB", "full": "Memorial Union Building"};
	buildingNames[37] = {"abbr": "Wads", "full": "Wadsworth Hall"};
	buildingNames[38] = {"abbr": "West McNair", "full": "West McNair Hall"};
	buildingNames[40] = {"abbr": "East McNair", "full": "East McNair Hall"};
	buildingNames[46] = {"abbr": "Nordic Ski Waxing Center", "full": "Nordic Ski Waxing Center (Tech Trails)"};
	buildingNames[48] = {"abbr": "Hillside", "full": "Hillside Place"};
	buildingNames[50] = {"abbr": "Gates Tennis Center", "full": "Gates Tennis Center"};
	buildingNames[52] = {"abbr": "Golf Course", "full": "Portage Lake Golf Course"};
	buildingNames[53] = {"abbr": "Mont Ripley", "full": "Mont Ripley Ski Hill"};
	buildingNames[54] = {"abbr": "Mont Ripley Chalet", "full": "Mont Ripley Ski Hill Chalet"};
	buildingNames[66] = {"abbr": "Nordic Ski Timing Building", "full": "Nordic Ski Timing Building"};
	buildingNames[67] = {"abbr": "Nordic Ski Warmup Building", "full": "Nordic Ski Warmup Building"};
	buildingNames[84] = {"abbr": "Meese Center", "full": "Harold Meese Center"};
	buildingNames[95] = {"abbr": "ATDC", "full": "Advanced Technology Development Complex"};
	buildingNames[100] = {"abbr": "GLRC", "full": "Great Lakes Research Center"};
	buildingNames[103] = {"abbr": "Mineral Museum", "full": "A.E. Seaman Mineral Museum"};
	buildingNames[801] = {"abbr": "SDC Soccer Fields", "full": "SDC Soccer Fields"};
	buildingNames[802] = {"abbr": "Sherman Field", "full": "Sherman Field"};
	buildingNames[803] = {"abbr": "Disc Golf Course", "full": "Disc Golf Course"};
	buildingNames[804] = {"abbr": "Recreational Sports Fields", "full": "Recreational Sports Fields"};
	buildingNames[805] = {"abbr": "Broomball Courts", "full": "Broomball Courts"};


/**
 * Gets course info from server and puts it into course info pop-up box
 */
function loadCourseInfo(courseNum) {
	var newHtml = "";
	var semester = $('#semester').val();
	$.get('/public/getCourseInfo/' +semester + "/" + courseNum, function(responseTxt) {

		var info = responseTxt[courseNum];
		newHtml += "<h1 class='text-center'>" + courseNum + " " + info.CourseName + "</h1>";
		newHtml += info.Credits + " Credits (" + info.LectureCredits + " Lec/" + info.RecitationCredits + " Rec/" + info.LabCredits + " Lab) | ";
		newHtml += "Offered " + info.SemestersOffered + " Semesters<br/>";
		newHtml += "<hr/>";
		newHtml += info.Description + "<br/><br/>";

		newHtml += "<h3>Sections</h3>";
		$.each(info.SectionInfo, function(CRN, sectionInfo){
			newHtml += "<p>" + sectionInfo.Semester + " " + sectionInfo.SectionNum + " " + CRN + ": " + sectionInfo.Days + " " + sectionInfo.SectionTime;
			newHtml += " " + locationText(sectionInfo.Location, 'html');
			newHtml += " - " + sectionInfo.Instructor + " ";
			var badgeColor = getBadgeColor(sectionInfo.Capacity - sectionInfo.SectionActual);
			newHtml += "<span class='badge badge-"+badgeColor+"' data-toggle='tooltip' data-placement='top' title='Filled Slots'>";
				newHtml += sectionInfo.SectionActual + "/" + sectionInfo.Capacity;
			newHtml += "</span>";
			newHtml += "</p>";
		});

		newHtml += "<h3>Restrictions</h3>";
		if(info.Restrictions != null) newHtml += info.Restrictions + "<br/><br/>";

		//Parse pre-req information
		newHtml += "<b style='text-align:left;'>Pre-Requisites:</b>";
		newHtml += "<ul style='text-align:left;'>";

		var allReqs = [];
		//var courseNumList;
		if(info.Prereq != null) {
			allReqs = info.Prereq.replace(/[()]+/g,''); //get rid of (, )
			allReqs = allReqs.replace(/[&|]+/g, ','); //separate all courses by ,
			allReqs = allReqs.split(","); //split into an array of all courses
		}

		courseNumList = allReqs.join();

		//Add blank pre-req if necessary
		if(courseNumList.length == 0) {
			allReqs.push("");
			newHtml += "<li><i>None</i></li>";
		}

		$.get('/public/getPreReqCourseNames/'+courseNumList, function(responseTxt) {
			var courseNamesList = responseTxt;

			var req = [];
			if(info.preReq != null) {
				req = info.Prereq.replace(/[()]+/g,'');
				req = req.split("&");
			}

			for(var i = 0; i < req.length; i++) {
				var orData = req[i].split("|");
				newHtml += "<li>";

				for(var j = 0; j < orData.length; j++) {
					var completedColor = "";
					if(courseNamesList[j].isComplete) {
						completedColor = "green";
					}else{
						completedColor = "red";
					}

					newHtml += "<span data-toggle='tooltip' style='color:"+completedColor+"' data-placement='top' title='"+courseNamesList[j].courseName+"'>"+orData[j]+"</span>";
					if(j+1 < orData.length) newHtml += " OR ";
				}
				newHtml += "</li>";
			}

			newHtml += "</ul>";
			newHtml += "<br/><br/>";
			newHtml += "<i style='font-size:.75em;'>Updated "+info.lastModified+"</i>";

			$('.courseInfoBox>.modal-dialog>.modal-content').html(newHtml);
			infoBoxEventListeners();
		});
	});
}


function getCourseNamesList(courseNumList) {
	courseNumList = courseNumList.join();
	$.get('/public/getPreReqCourseNames/'+courseNumList, function(responseTxt) {
		return responseTxt.split(',');
	});
}

/**
 * Converts a building number into a building name and room number
 * @param  {[String]} location [A string containing building number and room number separated by space]
 * @param  {[String]} mode     ["html" or "text" - output mode]
 */
function locationText(location, mode) {
	try{
		var buildingNum = parseInt(location.split(" ")[0]);
		var roomNum = location.split(" ")[1];
		var buildingName = buildingNames[buildingNum];
		var fullLocation = buildingName.full+" "+roomNum;
		var abbrLocation = buildingName.abbr+" "+roomNum;
	} catch(e) {
		var fullLocation = "Unknown Location";
		var abbrLocation = "Unknown Location";
	}

	if(mode == "html"){
		return "<span title='"+fullLocation+"'>"+abbrLocation+"</span>";
	}else{
		return abbrLocation;
	}
}

/**
 * Generates text for remove button
 */
function removeButton(CRN) {
	var removeButton = "";
	$.each(courseList, function(i, Course){
		if(Course.crn == CRN){
			var courseNum = 
			removeButton = `<a href='#' class='badge badge-danger ml-1 removeBtn'
							data-crn='`+CRN+`' data-credits='`+Course.credits+`' data-toggle='tooltip' title='Remove this section from your calendar' data-placement='top'>REMOVE</a>`;
		}
	});

	return removeButton;
}


/**
 * Determines badge color based on remaining slots in class
 */
function getBadgeColor(slotsRemaining) {
	var badgeColor = "primary";
	switch(true){
		case slotsRemaining < 5:
			badgeColor = "danger";
			break;

		case slotsRemaining < 10:
			badgeColor = "warning";
			break;
	}
	return badgeColor;
}

/**
 * Search for course - triggered whenever text in searchBox changes
 */
function search() {
	var query = $('#searchBox').val();
	var semester = $('#semester').val();
	$.get('/public/search/' + semester + "/" + query, function(responseTxt, status) {
		try {
			var data = responseTxt;
			displaySearchResults(data);
		} catch(e) {
			displayNoResults();
		}
	});
}

/**
 * Parse and display search results data
 */
function displaySearchResults(data) {
	var html = "";
	$.each(data, function(courseNum, e){
		html += "<li class='list-group-item'>";
		html += "<b>" + courseNum + ": " + e.CourseName + "</b>";
		html += "<a href='#' class='badge badge-primary ml-1' data-toggle='modal' data-target='.courseInfoBox' data-coursenum='"+courseNum+"'>Info</a>";
		html += "<br/>";
		html += e.Description + "<br/><br/>";

		//Display Sections:
		$.each(e.SectionInfo, function(CRN, sectionData){
			html += "<p>";
				html += "<a href='#' class='badge badge-success mr-1 addCourseBtn' data-coursecrn='"+CRN+"' data-coursenum='"+courseNum+"'>Add</a>";
				html += sectionData.Semester + " " + sectionData.SectionNum + ": " + sectionData.Days + " " + sectionData.SectionTime;
				html += " - " + "<a href='http://www.ratemyprofessors.com/search.jsp?query="+sectionData.Instructor+"+mtu' target='_new'>"+sectionData.Instructor+"</a>";
				var badgeColor = getBadgeColor(sectionData.Capacity - sectionData.SectionActual);
				html += " <span class='badge badge-"+badgeColor+"' data-toggle='tooltip' data-placement='top' title='Filled Slots'>";
					html += sectionData.SectionActual + "/" + sectionData.Capacity;
				html += "</span>";
			html += "</p>";
		});

		html += "</li>";
	});

	$("#searchResults").html(html);
	listenForSearchClickEvents();
}

/* Special case to display no results message */
function displayNoResults() {
	$("#searchResults").html("<li class='list-group-item'><i>No Results.</i></li>");
}