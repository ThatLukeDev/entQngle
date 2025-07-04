<?php

session_start();

require_once "config.php";

require_once "pqkx.php";

require_once "totp.php";

$totpkey = $_SESSION["totpkey"];
$otpauth = genotpauth($_SESSION["usrsub"], $totpkey);

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["code"]) {
	if (preg_replace("/[^0-9]/", "", $_POST["code"]) == totp($totpkey)) {
		$stmt = $mysqli->prepare("insert into users values (?, ?, ?)");
		$stmt->bind_param("sss", $_SESSION["usrsub"], $_SESSION["passhashsub"], base64_encode($totpkey));
		$stmt->execute();

		header("Location: signin.php");
	}
	else {
		$error = "Wrong key";
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>entQngle</title>
	</head>
	<body>
		<div id="d2Code"></div>
		<br>
		<div class="pass" id="displayCode"><?php echo pqkx_encrypt($otpauth); ?></div>

		<form id="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
			Code:<br>
			<input type="text" name="code" value=""><br>

			<input type="submit" value="Sign up">
			<a class="error"><?php echo $error; if (!empty($error)) echo "<br>"; ?></a><br>
		</form>
		<style>
			.error {
				color: red;
			}
		</style>
		<script src="qr.js"></script>
		<script>
			document.querySelectorAll(".pass").forEach((v) => {
				v.innerHTML = pqkx_decrypt(v.innerHTML);
			});
			let otp = document.querySelector("#displayCode").innerHTML.replace("&amp;","&");
			document.querySelector("#d2Code").appendChild(QR4.code(otp, 10));
		</script>
	</body>
</html>
