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

$app->get('/updateSections', function(Request $request, Response $response) {
	$scraper = new Scraper($this->db);
	$result = $scraper->generateBanwebFiles();
	echo $result;
	//$response = $response->withJson($result);
	return $response;
});

$app->get('/test', function(Request $request, Response $response) {
	$response = $response->withJson($_ENV['env_name']);
	return $response;
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


$app->get('/saveScheduleAs/{name}/{year}/{semester}/{CRNList}', function(Request $request, Response $response, $args) {
	$ScheduleName = $args['name'];
	$Year = $args['year'];
	$Semester = $args['semester'];
	$CRNList = $args['CRNList'];

	$scheduleMapper = new ScheduleMapper($this->db);
	$scheduleId = $scheduleMapper->saveScheduleAs($ScheduleName, $Semester, $Year, $CRNList);
	return $response->withJson(['id' => $scheduleId]);
});

$app->get('/saveSchedule/{id}/{CRNList}', function(Request $request, Response $response, $args) {
	$id = $args['id'];
	$CRNList = $args['CRNList'];

	$scheduleMapper = new ScheduleMapper($this->db);
	$result = $scheduleMapper->saveSchedule($id, $CRNList);

});

$app->get('/getScheduleList', function(Request $request, Response $response, $args) {

	$scheduleMapper = new ScheduleMapper($this->db);
	$scheduleList = $scheduleMapper->getScheduleList();

	return $response->withJson($scheduleList);
});

$app->get('/openSchedule/{id}', function(Request $request, Response $response, $args) {
	$id = $args['id'];

	$scheduleMapper = new ScheduleMapper($this->db);
	$CRNList = $scheduleMapper->openSchedule($id);

	return $response->withJson($CRNList);
});

$app->get('/getScheduleInfo/{id}', function(Request $request, Response $response, $args) {
	$id = $args['id'];

	$scheduleMapper = new ScheduleMapper($this->db);
	$scheduleInfo = $scheduleMapper->getScheduleInfo($id);

	return $response->withJson($scheduleInfo);
});

$app->get('/getPreReqCourseNames/{courseList}', function(Request $request, Response $response, $args) {
	$courseNumList = explode(",", $args['courseList']);
	$completedCoursesMapper = new CompletedCoursesMapper($this->db);
	$courseNamesList = $completedCoursesMapper->getPreReqCourseNames($courseNumList);
	return $response->withJson($courseNamesList);
});

$app->get('/completedCourses/subjectList', function(Request $request, Response $response) {
	$completedCoursesMapper = new CompletedCoursesMapper($this->db);
	$subjectList = $completedCoursesMapper->getSubjects();
	return $response->withJson($subjectList);
});

$app->get('/completedCourses/coursesInSubj/{subject}', function(Request $request, Response $response, $args) {
	$subject = $args['subject'];

	$completedCoursesMapper = new CompletedCoursesMapper($this->db);
	$courseList = $completedCoursesMapper->getCoursesInSubj($subject);
	return $response->withJson($courseList);
});

$app->get('/completedCourses/markComplete/{courseNum}/{subject}', function(Request $request, Response $response, $args) {
	$subject = $args['subject'];
	$courseNum = $args['courseNum'];

	$completedCoursesMapper = new CompletedCoursesMapper($this->db);
	$result = $completedCoursesMapper->markComplete($courseNum, $subject);
	return $response->withJson($result);
});

$app->get('/completedCourses/markIncomplete/{courseNum}', function(Request $request, Response $response, $args) {
	$courseNum = $args['courseNum'];

	$completedCoursesMapper = new CompletedCoursesMapper($this->db);
	$result = $completedCoursesMapper->markInComplete($courseNum);
	return $response->withJson($result);
});