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
		// TODO
	}
}
else {
	$error = "User not specified";
}

require_once "rlwe-func.php";

require_once "localkey.php";

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
				let id = data.split(";")[1];

				let init = data.split(";")[0].split(",");
				init[0] = JSON.parse(atob(init[0]));
				init[1] = JSON.parse(atob(init[1]));

				let response = respondRLWE(init[0], init[1]);

				let txtResponse = `${btoa(JSON.stringify(response[0]))},${btoa(JSON.stringify(response[1]))}`;

				let key = [];
				for (let i = 0; i < keysizeRLWE / 8; i++) {
					key[i] = 0;
				}
				for (let i = 0; i < keysizeRLWE; i++) {
					key[Math.floor(i / 8)] |= response[2][i] << (i % 8);
				}

				let tmpkey = key.slice();
				let out = "";
				for (let i = 0; i < message.length; i++) {
					tmpkey = rollkey(tmpkey.slice());
					out += String.fromCharCode(message.charCodeAt(i) ^ tmpkey[tmpkey.length - 1]);
				}
				out = btoa(out);

				let outputresponse = `${out};${txtResponse};${id}`;

				fetch("<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?user=<?php echo htmlspecialchars($_GET["user"]); ?>", {
					headers: {
						"Content-Type": "application/x-www-form-urlencoded"
					},
					method: "POST",
					body: `message=${outputresponse}`
				});
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
