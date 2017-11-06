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

	public function verifyIdToken($idToken, $email, $name) {
		$client = new Google_Client(['client_id' => $CLIENT_ID]);
		$payload = $client->verifyIdToken($idToken);
		if ($payload) {
		  $userid = $payload['sub'];
		  if(self::userExists($userid)){
		  	self::login($userid, $email, $name);
		  }else{
		  	self::createUser($userid, $email, $name);
		  }
		  // If request specified a G Suite domain:
		  //$domain = $payload['hd'];
		} else {
		  return "UserId is invalid!";
		}
	}

	private function userExists($userId) {
		$stmt = $this->db->prepare("SELECT COUNT(*) FROM Users WHERE GoogleId=:userId");
		$stmt->execute([
			'userId' => $userId
		]);
		$numUsers = $stmt->fetchColumn();

		echo $numUsers;

		return $numUsers == 1;
	}

	private function login($userId, $email, $name) {

		$_SESSION['userId'] = $userId;
		$_SESSION['email'] = $email;
		$_SESSION['name'] = $name;

	}

	private function createUser($userId, $email, $name) {
		$stmt = $this->db->prepare("INSERT INTO Users (GoogleId, Email) VALUES (:userId, :email)");
		$stmt->execute([
			'userId' => $userId,
			'email' => $email
		]);

		//now login.
		self::login($userId);
	}
}

?>