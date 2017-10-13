<?php

class UserMapper extends Mapper {

	public function getUsers() {
		$stmt = $this->db->query("SELECT * FROM test");
		$results = [];

		while($row = $stmt->fetch()) {
			$results[] = $row['username'];
		}

		return json_encode($results);
	}

	public function addUser($username) {
		$sql = "INSERT INTO test (username) VALUES (:username)";
		$stmt = $this->db->prepare($sql);
		$result = $stmt->execute([
			"username" => $username
		]);

		if(!$result) throw new Exception("Could not add username.");
	}
}

?>