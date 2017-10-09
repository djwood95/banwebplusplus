<html>
	<head>
		<script
		  src="https://code.jquery.com/jquery-1.12.4.min.js"
		  integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
		  crossorigin="anonymous"></script>

		<script>
			function insertUsername() {
				var username = $('#usernameTest').val();
				$.post('addUserTest.php', {username}, function() {
					alert("Username Added! Refresh page to see changes.");
				});
			}
		</script>
</head>
<body>
	Enter Username Test: <input type='text' id='usernameTest' onchange='insertUsername()'/>	
</body>

<?php

require_once("includes/db.php") or die("error loading database config");

$stmt = $con->prepare("SELECT * FROM test");
$stmt->execute() or die("SQL Error" . $stmt->error);
$res1 = $stmt->get_result();
$stmt->close();

while($row = $res1->fetch_array()){
	$date = date("Y-m-d", strtotime($row[datetime]));
	echo "$row[username]";
	echo " | ";
	echo "$date";
	echo "<br/>";
}

?>