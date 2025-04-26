<?php
require_once "config.php";

$username = "";
$password = "";
$password2 = "";

$username_error = "";
$password_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (empty($_POST["username"])) {
		$username_error = "Username must not be empty";
	}
	else if (strlen($_POST["username"]) > 20) {
		$username_error = "Username must be less than 20 characters";
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
