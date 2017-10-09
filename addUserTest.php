<?php

require_once("includes/db.php");

$stmt = $con->prepare("INSERT INTO test (username) VALUES (?)");
$stmt->bind_param("s", $_POST[username]);
$stmt->execute() or die("SQL Error: " . $stmt->error);
$stmt->close();

?>