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

/* Search for course by name (Intro to Programming) or title (CS 1121)
	returns JSON object */
$app->get('/search/{query}', function(Request $request, Response $response, $args) {
	$query = $args['query'];
	$courseMapper = new CourseMapper($this->db);
	$results = $courseMapper->search($query);
	echo $results;
});

$app->get('/search/', function(Request $request, Response $response, $args) {
	echo "Please enter search query";
});