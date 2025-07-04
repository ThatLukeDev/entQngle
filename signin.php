<?php
require_once "config.php";

require_once "totp.php";

require_once "pqkx.php";
session_start();

$username = "";
$password = "";
$totp = "";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = strtolower(pqkx_decrypt($_POST["username"]));
	$password = pqkx_decrypt($_POST["password"]);
	$totp = pqkx_decrypt($_POST["totp"]);
	$stmt = $mysqli->prepare("select password, totp from users where username = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_row();
	$password_hash = $row[0];
	$totpcode = base64_decode($row[1]);
	if (!$password_hash) {
		$error = "User does not exist";
	}
	else if (!password_verify($password, $password_hash)) {
		$error = "Password does not match";
	}
	else if (preg_replace("/[^0-9]/", "", $totp) != totp($totpcode)) {
		$error = "One-Time-Code does not match";
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
		<form id="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
			Username<br>
			<input class="pass" id="username" type="text" name="username" value="<?php echo pqkx_encrypt($username); ?>"><br><br>

			Password<br>
			<input class="pass" type="password" name="password" value="<?php echo pqkx_encrypt($password); ?>"><br>

			One-Time-Code<br>
			<input class="pass" type="text" name="totp" value="<?php echo pqkx_encrypt($totp); ?>"><br>

			<a class="error"><?php echo $error; if (empty($error)) echo "<br>"; ?></a><br><br>

			<input type="submit" value="Sign in">
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
