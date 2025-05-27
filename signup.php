<?php
require_once "config.php";

require_once "pqkx.php";
session_start();

$username = "";
$password = "";
$password2 = "";

$username_error = "";
$password_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = strtolower(pqkx_decrypt($_POST["username"]));
	$password = pqkx_decrypt($_POST["password"]);
	$password2 = pqkx_decrypt($_POST["password2"]);

	if (empty($username)) {
		$username_error = "Username must not be empty";
	}
	else if (strlen($username) > 20) {
		$username_error = "Username must be less than 20 characters";
	}
	else if (preg_match("/[^A-Za-z0-9]/", $username)) {
		$username_error = "Username must consist of only letters and numbers";
	}
	else {
		$stmt = $mysqli->prepare("select username from users where username = ?");
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->fetch_row()) {
			$username_error = "Username already exists";
		}
	}

	if (empty($password)) {
		$password_error = "Password must not be empty";
	}
	else if ($password != $password2) {
		$password_error = "Passwords do not match";
	}

	if (!$username_error && !$password_error) {
		$password_hash = password_hash($password, PASSWORD_DEFAULT);
		$stmt = $mysqli->prepare("insert into users values (?, ?)");
		$stmt->bind_param("ss", $username, $password_hash);
		$stmt->execute();

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
		<form id="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
			Username<br>
			<input class="pass" type="text" name="username" value="<?php echo pqkx_encrypt($username); ?>"><br>
			<a class="error"><?php echo $username_error; if (!empty($username_error)) echo "<br>"; ?></a><br>

			Password<br>
			<input class="pass" type="password" name="password" value="<?php echo pqkx_encrypt($password); ?>"><br>
			Confirm Password<br>
			<input class="pass" type="password" name="password2" value="<?php echo pqkx_encrypt($password2); ?>"><br>
			<a class="error"><?php echo $password_error; if (!empty($password_error)) echo "<br>"; ?></a><br>

			<input type="submit" value="Sign up">
		</form>
	</body>
	<script>
		document.querySelectorAll(".pass").forEach((v) => {
			v.value = pqkx_decrypt(v.value);
		});
		document.querySelector("#form").onsubmit = () => {
			document.querySelectorAll(".pass").forEach((v) => {
				v.value = pqkx_encrypt(v.value);
			});
		};
	</script>
	<style>
		.error {
			color: red;
		}
	</style>
</html>
