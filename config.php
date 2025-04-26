<?php

require_once "secret.php";

$mysqli = mysqli_connect(SQLSERVER, SQLUSER, SQLPASS, SQLDBNAME);

if ($mysqli->connect_error) {
	die("Could not connect to SQL Server: " . $mysqli->connect_error);
}

?>
