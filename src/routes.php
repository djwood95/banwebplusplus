<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

//Get Username list from DB (test)
$app->get('/', function (Request $request, Response $response) {
	$mapper = new UserMapper($this->db);
	$users = $mapper->getUsers();
	echo $users;
});

//Add Username to DB (test)
$app->get('/newUser/{username}', function (Request $request, Response $response, $args) {
	$username = $args['username'];
	$mapper = new UserMapper($this->db);
	$users = $mapper->addUser($username);
});

//Begin scraping process by downloading HTML from Banweb
$app->get('/generateBanwebFiles', function(Request $request, Response $response) {
	$scraper = new Scraper($this->db);
	$scraper->generateBanwebFiles();
});

//Begin scraping process by downloading HTML from Banweb
$app->get('/descriptions', function(Request $request, Response $response) {
	$scraper = new Scraper($this->db);
	$scraper->getCourseDescriptions();
});

$app->get('/search/{query}', function(Request $request, Response $response, $args) {
	$query = $args['query'];
	$courseMapper = new CourseMapper($this->db);
	$results = $courseMapper->search($query);
	echo $results;
});