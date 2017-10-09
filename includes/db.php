<?php
	
	function db_connect() {
		static $con; // con = connection

		if(!isset($con)) {
			$config = parse_ini_file("../private/config.ini");
			$con = mysqli_connect($config['servername'], $config['username'], $config['password'], $config['dbname']);
		}

		if(!$con) {
			return mysqli_connection_error();
		}

		return $con;
	}

	$con = db_connect();

	if($con->connect_error) {
		die("Connection Failed with Error: " . $con->connection_error);
	}

?>