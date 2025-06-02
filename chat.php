<?php

require_once "pqkx.php";
session_start();

?>

<?php

$user = "";

if ($_GET["user"]) {
	$user = htmlspecialchars(pqkx_decrypt($user));
}
else {
	$error = "User not specified";
}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>entQngle</title>
	</head>
	<body>
		<button id="homeBtn">Home</button>
		<br>

		<h1>entQngle</h1>

		<a class="error"><?php echo $error; if (empty($error)) echo "<br>"; ?></a><br><br>

		<form id="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
			Message<br>
			<textarea class="msgbox sensitive" type="text" name="message"></textarea><br><br>

			<input class="submit" type="submit" value="Send">
		</form>
	</body>
	<script>
		document.querySelector("#homeBtn").onclick = () => {
			window.location.href = "index.php";
		};
	</script>
	<style>
		.error {
			color: red;
		}
		.msgbox {
			width: 80vw;
			height: 50vh;
		}
		.submit {
			width: 5vw;
			height: 5vh;
		}
	</style>
</html>
