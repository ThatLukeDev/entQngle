<?php
require_once "config.php";

$username = "";
$password = "";
$password2 = "";

$username_error = "";
$password_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = strtolower($_POST["username"]);
	$password = $_POST["password"];
	$password2 = $_POST["password2"];

	if (empty($username)) {
		$username_error = "Username must not be empty";
	}
	else if (strlen($username) > 20) {
		$username_error = "Username must be less than 20 characters";
	}
	else if (preg_match("/[^A-Za-z0-9]/", $username)) {
		$username_error = "Username must consist of only letters and numbers";
	}
	else if (mysqli_query($mysqli, "select username from users where username like '{htmlspecialchars($username)}'")->fetch_row()) {
		$username_error = "Username already exists";
	}

	if (empty($password)) {
		$password_error = "Password must not be empty";
	}
	else if ($password != $password2) {
		$password_error = "Passwords do not match";
	}

	if (!$username_error && !$password_error) {
		header("Location: signin.php");
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>entQngle</title>
	</head>
	<body>
		<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET">
			Username<br>
			<input type="text" name="username" value="<?php echo $username; ?>"><br>
			<a class="error"><?php echo $username_error; if (!empty($username_error)) echo "<br>"; ?></a><br>

			Password<br>
			<input type="password" name="password" value="<?php echo $password; ?>"><br>
			Confirm Password<br>
			<input type="password" name="password2" value="<?php echo $password2; ?>"><br>
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
