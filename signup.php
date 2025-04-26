<?php
require_once "config.php";

$username = "";
$password = "";
$password2 = "";

$username_error = "";
$password_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = $_POST["username"];

	if (empty($username)) {
		$username_error = "Username must not be empty";
	}
	else if (strlen($username) > 20) {
		$username_error = "Username must be less than 20 characters";
	}
	else if (mysqli_query($mysqli, "select username from users where username like '{$username}'")->fetch_row()) {
		$username_error = "Username already exists";
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
