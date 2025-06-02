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

// these are public keys, and will be given to whoever asks. no need for protections
mysqli_query($mysqli, "create table if not exists userkeys(
	username varchar(20),
	key TEXT CHARACTER SET latin1,
	PRIMARY KEY (username)
);");

// the sent key is a part of the keysharing process, no need for protections
mysqli_query($mysqli, "create table if not exists messages(
	from varchar(20),
	to varchar(20),
	key TEXT CHARACTER SET latin1,
);");

?>
