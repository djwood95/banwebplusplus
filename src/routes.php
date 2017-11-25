<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

//Add Username to DB (test)
$app->get('/newUser/{username}', function (Request $request, Response $response, $args) {
	$username = $args['username'];
	$mapper = new UserMapper($this->db);
	$users = $mapper->addUser($username);
});

$app->get('/getAvailableSemesters', function(Request $request, Response $response) {
	$courseMapper = new CourseMapper($this->db);
	$semesterList = $courseMapper->getAvailableSemesters();
	$response = $response->withJson($semesterList);
	return $response;
});

/* Search for course by name (Intro to Programming) or title (CS 1121)
	returns JSON object */
$app->get('/search/{semester}/{query}', function(Request $request, Response $response, $args) {
	$query = $args['query'];
	$semester = $args['semester'];
	$courseMapper = new CourseMapper($this->db);
	$results = $courseMapper->search($query, $semester);
	$response = $response->withJson($results);
	return $response;
});

$app->get('/search/{semester}/', function(Request $request, Response $response, $args) {
	$results = "Please enter a search query.";
	$response = $response->withJson($results);
	return $response;
});

$app->get('/getCourseInfo/{semester}/{courseNum}', function(Request $request, Response $response, $args) {
	$query = $args['courseNum'];
	$semester = $args['semester'];
	$courseMapper = new CourseMapper($this->db);
	$results = $courseMapper->getCourseInfo($query, $semester);
	$response = $response->withJson($results);
	return $response;
});

$app->get('/getCourseInfoForCalendar/{crn}/{courseNum}', function(Request $request, Response $response, $args) {
	$crn = $args['crn'];
	$courseNum = $args['courseNum'];
	$courseMapper = new CourseMapper($this->db);
	$results = $courseMapper->getCourseInfoForCalendar($crn, $courseNum);
	$response = $response->withJson($results);
	return $response;
});

$app->get('/search/', function(Request $request, Response $response, $args) {
	echo "Please enter search query";
});

$app->get('/isLoggedIn', function(Request $request, Response $response, $args) {
	$userMapper = new UserMapper($this->db);
	$results = $userMapper->isLoggedIn();
	return $response->withJson($results);
});

$app->get('/logout', function(Request $request, Response $response, $args) {
	$_SESSION = array();
});

$app->post('/verifyIdToken', function(Request $request, Response $response) {
	$idToken = $request->getParsedBody()['idtoken'];
	$email = $request->getParsedBody()['email'];
	$name = $request->getParsedBody()['name'];
	$userMapper = new UserMapper($this->db);
	$result = $userMapper->verifyIdToken($idToken, $email, $name);
	return $result;
});


$app->get('/addSchedule/{name}/{year}/{semester}', function(Request $request, Response $response)
{
	console.log("Got to AddSchedule");
	$ScheduleName = $args['name'];
	$UserID = gapi.auth2.getAuthInstance().currentUser.get().getBasicProfile().getEmail();
	$Year = $args['year'];
	$Semester = $args['semester'];
	console.log($UserID);

	$scheduleMapper = new ScheduleMapper($this->db);
	$scheduleMapper->AddSchedule($ScheduleName, $UserID, $Semester, $Year);
	console.log("Finished AddSchedule");
});
		  

$app->get('/addCourseToCalendar/{scheduleName}/{crn}', function(Request $request, Response $response)
{
	console.log("Got to AddCourseToCalendar");
	$ScheduleName = $args['scheduleName'];
	$UserID = gapi.auth2.getAuthInstance().currentUser.get().getBasicProfile().getEmail();
	$CRN = $args['crn']
	console.log($UserID);

	$scheduleMapper = new ScheduleMapper($this->db);
	$scheduleMapper->AddCourseToSchedule($ScheduleName, $UserID, $CRN);
	console.log("Finished AddCourseToCalendar");
});

$app->get('/getPreReqCourseNames/{courseList}', function(Request $request, Response $response, $args) {
	$courseNumList = $args['courseList'];
	$completedCoursesMapper = new CompletedCoursesMapper($this->db);
	$courseNamesList = $completedCoursesMapper->getPreReqCourseNames();
	return $response->withJson($courseNamesList);
});

$app->get('/completedCourses/subjectList', function(Request $request, Response $response) {
	$completedCoursesMapper = new completedCoursesMapper($this->db);
	$subjectList = $completedCoursesMapper->getSubjects();
	return $response->withJson($subjectList);
});

$app->get('/completedCourses/coursesInSubj/{subject}', function(Request $request, Response $response, $args) {
	$subject = $args['subject'];

	$completedCoursesMapper = new completedCoursesMapper($this->db);
	$courseList = $completedCoursesMapper->getCoursesInSubj($subject);
	return $response->withJson($courseList);
});

$app->get('/completedCourses/markComplete/{courseNum}/{subject}', function(Request $request, Response $response, $args) {
	$subject = $args['subject'];
	$courseNum = $args['courseNum'];

	$completedCoursesMapper = new completedCoursesMapper($this->db);
	$result = $completedCoursesMapper->markComplete($courseNum, $subject);
	return $response->withJson($result);
});

$app->get('/completedCourses/markIncomplete/{courseNum}', function(Request $request, Response $response, $args) {
	$courseNum = $args['courseNum'];

	$completedCoursesMapper = new completedCoursesMapper($this->db);
	$result = $completedCoursesMapper->markInComplete($courseNum);
	return $response->withJson($result);
});