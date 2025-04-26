<?php
require_once "secret.php";

$username = "";
$password = "";
$password2 = "";

$username_error = "";
$password_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username_error = "test";
}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>entQngle</title>
	</head>
	<body>
		<form action="signup.php" method="GET">
			Username<br>
			<input type="text" name="username"><br>
			<a class="error"><?php echo $username_error; if (!empty($username_error)) echo "<br>"; ?></a><br>

			Password<br>
			<input type="password" name="password"><br>
			<a class="error"><?php echo $password_error; if (!empty($password_error)) echo "<br>"; ?></a><br>

			<input type="submit" value="Sign up">
		</form>
	</body>
	<style>
		.error {
			color: red;
		}
	</style>
</html>
