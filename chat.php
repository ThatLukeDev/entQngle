<?php

require_once "config.php";

session_start();

require_once "pqkx.php";

?>

<?php

$user = "";

if ($_GET["user"]) {
	$user = htmlspecialchars(pqkx_decrypt($_GET["user"]));

	if ($_POST["getUserPublicKey"]) {
		ob_clean();
		$stmt = $mysqli->prepare("select keyvk, keyid from userkeys where username = ?");
		$stmt->bind_param("s", $user);
		$stmt->execute();
		$result = $stmt->get_result()->fetch_row();
		echo $result[0].";".$result[1];
		return;
	}
	else if ($_POST["message"]) {
	}
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
			<textarea class="msgbox sensitive" id="msgcontent" type="text" name="message"></textarea><br><br>

			<input class="submit" type="submit" value="Send">
		</form>
	</body>
	<script>
		document.querySelector("#homeBtn").onclick = () => {
			window.location.href = "index.php";
		};
		document.querySelector("#form").onsubmit = () => {
			let message = document.querySelector("#msgcontent").value;
			document.querySelector("#msgcontent").value = "";

			fetch("<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?user=<?php echo htmlspecialchars($_GET["user"]); ?>", {
				headers: {
					"Content-Type": "application/x-www-form-urlencoded"
				},
				method: "POST",
				body: "getUserPublicKey=true"
			}).then(response => response.text())
			.then(data => {
				console.log(data);
			});
			return false;
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
			width: 10vw;
			height: 5vh;
		}
	</style>
</html>
