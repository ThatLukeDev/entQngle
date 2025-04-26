<?php

require_once "secret.php";

$mysqli = mysqli_connect(SQLSERVER, SQLUSER, SQLPASS, SQLDBNAME);

if ($mysqli->connect_error) {
	die("Could not connect to SQL Server: " . $mysqli->connect_error);
}

mysqli_select_db($mysqli, SQLDBNAME);

mysqli_query($mysqli, "create table if not exists users(
	username varchar(20),
	password varchar(255),
	PRIMARY KEY (username)
);");

?>
