<?php
require_once "config.php";

$username = "";
$password = "";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = strtolower($_POST["username"]);
	$password = $_POST["password"];
	$password_hash = mysqli_query($mysqli, "select password from users where username like '".htmlspecialchars($username)."'")->fetch_row()[0];
	if (!$password_hash) {
		$error = "User does not exist";
	}
	else if (!password_verify($password, $password_hash)) {
		$error = "Password does not match";
	}
	else {
		session_start();

		$_SESSION["username"] = htmlspecialchars($username);

		header("Location: /");
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
			<input type="text" name="username" value="<?php echo $username; ?>"><br><br>

			Password<br>
			<input type="password" name="password" value="<?php echo $password; ?>"><br>
			<a class="error"><?php echo $error; if (empty($error)) echo "<br>"; ?></a><br><br>

			<input type="submit" value="Sign in">
		</form>
	</body>
	<style>
		.error {
			color: red;
		}
	</style>
</html>
