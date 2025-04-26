<?php
require_once "config.php";

require_once "rlwe.php";
session_start();
autosessionRLWE();

$username = "";
$password = "";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = strtolower(rlwe_decrypt($_POST["username"]));
	$password = rlwe_decrypt($_POST["password"]);
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
		<form id="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET">
			Username<br>
			<input class="pass" type="text" name="username" value="<?php echo rlwe_encrypt($username); ?>"><br><br>

			Password<br>
			<input class="pass" type="password" name="password" value="<?php echo rlwe_encrypt($password); ?>"><br>
			<a class="error"><?php echo $error; if (empty($error)) echo "<br>"; ?></a><br><br>

			<input type="submit" value="Sign in">
		</form>
	</body>
	<script>
		document.querySelectorAll(".pass").forEach((v) => {
			v.value = rlwe_decrypt(v.value);
		});
		document.querySelector("#form").onsubmit = () => {
			document.querySelectorAll(".pass").forEach((v) => {
				v.value = rlwe_encrypt(v.value);
			});
		};
	</script>
	<style>
		.error {
			color: red;
		}
	</style>
</html>
