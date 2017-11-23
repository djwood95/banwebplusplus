$('document').ready(function() {

	$('.completedCoursesBox').on('show.bs.modal', function(event){
		loadCompletedCoursesBox();
	});

});


function loadCompletedCoursesBox() {
	$.get('/public/completedCourses/subjectList', function(responseTxt) {
		try {
			var newHtml = "";
			newHtml += "<div class='dropdown show'>";
				newHtml += "<a class='btn btn-secondary dropdown-toggle' href='#' role='button' id='dropdownMenuLink' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>";
    				newHtml += "<span id='selectedSubject'>Select a Subject</span>";
				newHtml += "</a>";

				newHtml += "<div class='dropdown-menu dropdown-menu-scroll' aria-labelledby='dropdownMenuLink'>";
					var subjectList = responseTxt;
					$.each(subjectList, function(i, subject) {
						newHtml += "<a class='dropdown-item' data-subject='"+subject+"' href='#''>"+subject+"</a>";
					});
				newHtml += "</div>";

			newHtml += "</div>";
			newHtml += "<br/>";
			newHtml += "<div id='completedCourses-courseList'></div>"; //to hold course list for selected subject

		} catch(e) {
			console.log(e);
		}

		//$('.completedCoursesBox').html(newHtml);
		$('.completedCoursesBox>.modal-dialog>.modal-content').html(newHtml);

		completedCoursesBoxEventListeners();
	});
}

function completedCoursesBoxEventListeners() {

	$('.dropdown-item').click(function() {
		var subject = $(this).data('subject');
		$('#selectedSubject').text(subject);
		getCoursesInSubj(subject);
	});

}

function courseListEventListeners() {

	$('.complete-course').click(function() {
		if($(this).hasClass('list-group-item-success')){
			unmarkCourse($(this));
		}else{
			markCourseComplete($(this));
		}
	});

}

function getCoursesInSubj(subject) {
	$.get('/public/completedCourses/coursesInSubj/'+subject, function(responseTxt) {
		var data = responseTxt;
		var newHtml = "<div class='list-group'>";
		$.each(data, function(i, courseData) {
			var completedClass = "";
			var checkmark = "";
			if(courseData.Completed){
				completedClass = "list-group-item-success";
				checkmark = "<span class='oi oi-check float-right'></span>";
			}
			newHtml += `<button type='button' class='complete-course complete-course-`+courseData.CourseNum+` list-group-item `+completedClass+`'
							data-coursenum='`+courseData.CourseNum+`' data-subject='`+subject+`'>`+courseData.CourseNum+`: `+courseData.CourseName+checkmark+`</li>`;
		});
		newHtml += "</div>";

		$('#completedCourses-courseList').html(newHtml);
		courseListEventListeners();
	});
}

function markCourseComplete(btn) {
	var courseNum = btn.data('coursenum');
	var subject = btn.data('subject');
	$.get('/public/completedCourses/markComplete/'+courseNum+'/'+subject, function(responseTxt) {
		if(responseTxt) {
			btn.addClass('list-group-item-success');
			btn.append("<span class='oi oi-check float-right'></span>");
		}
	});
}

function unmarkCourse(btn) {
	var courseNum = btn.data('coursenum');
	$.get('/public/completedCourses/markIncomplete/'+courseNum, function(responseTxt) {
		if(responseTxt) {
			btn.removeClass('list-group-item-success');
			btn.find('.oi-check').remove();
		}
	});
}