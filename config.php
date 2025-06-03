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
	keyvk varchar(8192),
	keyid varchar(256),
	PRIMARY KEY (username)
);");

// the sent key is a part of the keysharing process, no need for protections
mysqli_query($mysqli, "create table if not exists messages(
	fromusr varchar(20),
	tousr varchar(20),
	keyvk varchar(8192),
	keyid varchar(256),
	body text(32768)
);");

?>
