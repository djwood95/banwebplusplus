<?php

class CourseMapper extends Mapper {

	/** Search by course name or number **/
	function search($query) {
		$stmt = $this->db->prepare("SELECT * FROM Courses WHERE CourseNum LIKE :query OR CourseName LIKE :query_wildcard");
		$result = $stmt->execute([
			'query' => $query,
			'query_wildcard' => "%$query%"
		]);

		$results = [];

		while($row = $stmt->fetch()) {
			$results[] = $row; //all database columns into array
		}


		if(!$result) die("Could not add username.");

		if(count($results) == 0) return "$query returned no Results.";

		return json_encode($results);
	}

}


?>