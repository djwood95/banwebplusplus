$(document).ready(function(){

	$('#searchBox').on("input", function(){search();});

});

// For buttons embedded in search results, add event listeners here!!
function listenForSearchClickEvents() {
	$('.courseInfoBox').on('show.bs.modal', function(event){
		var clicker = $(event.relatedTarget);
		var courseNum = clicker.data('coursenum');
		loadCourseInfo(courseNum);
	});

	$('.addCourseBtn').on('click', function(event){
		var btn = $(event.relatedTarget);
		var crn = btn.data('crn');
		alert("Add Course to Calendar: CRN = " + btn);
	});
}

function loadCourseInfo(courseNum) {
	var newHtml = "";
	$.get('/public/getCourseInfo/' + courseNum, function(responseTxt){
		console.log(responseTxt);
		//var data = JSON.parse(responseTxt);
		var info = responseTxt[courseNum];
		newHtml += "<h1 class='text-center'>" + courseNum + " " + info.CourseName + "</h1>";
		newHtml += info.Credits + " Credits (" + info.LectureCredits + " Lec/" + info.RecitationCredits + " Rec/" + info.LabCredits + " Lab) | ";
		newHtml += "Offered " + info.SemestersOffered + " Semesters<br/>";
		newHtml += "Pre-Reqs: " + info.Prereq + " | Co-Reqs: " + info.Coreq;
		newHtml += " | Restrictions: " + info.Restrictions + "<hr/>"; 
		newHtml += info.Description + "<br/><br/>";

		newHtml += "<h3>Sections</h3>";
		$.each(info.SectionInfo, function(CRN, sectionInfo){
			newHtml += "<p>" + sectionInfo.Semester + " " + sectionInfo.SectionNum + " " + CRN + ": " + sectionInfo.Days + " " + sectionInfo.SectionTime;
			newHtml += " - " + sectionInfo.Instructor + " ";
			var badgeColor = getBadgeColor(sectionInfo.Capacity - sectionInfo.SectionActual);
			newHtml += "<span class='badge badge-"+badgeColor+"' data-toggle='tooltip' data-placement='top' title='Availabe Slots'>";
				newHtml += sectionInfo.SectionActual + "/" + sectionInfo.Capacity;
			newHtml += "</span></p>";
		});

		$('.courseInfoBox>.modal-dialog>.modal-content').html(newHtml);
	});
}

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

function search() {
	var query = $('#searchBox').val();
	console.log("Trying to search for " + query);
	$.get('/public/search/' + query, function(responseTxt, status) {
		console.log(status);
		try {
			var data = responseTxt;
			console.log(data);
			displaySearchResults(data);
		} catch(e) {
			console.log(e);
			displayNoResults();
		}
	});
}

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
				html += "<a href='#' class='badge badge-success mr-1 addCourseBtn' data-crn='"+CRN+"'>Add</a>";
				html += sectionData.Semester + " " + sectionData.SectionNum + ": " + sectionData.Days + " " + sectionData.SectionTime;
				html += " - " + sectionData.Instructor;
			html += "</p>";
		});

		html += "</li>";
	});

	$("#searchResults").html(html);
	listenForSearchClickEvents();
}

function displayNoResults() {
	$("#searchResults").html("<li class='list-group-item'><i>No Results.</i></li>");
}