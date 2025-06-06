<?php

require_once "config.php";

require_once "pqkx.php";
session_start();

if ($_POST["userspeaker"]) {
	$_SESSION["lastspoken"] = pqkx_decrypt($_POST["userspeaker"]);
	header("Location: chat.php?user=" . pqkx_encrypt($_SESSION["lastspoken"]));	// you can see who you are speaking with as an external viewer.
									// all traffic from the webpage should be treated as compromised,
									// this includes who you are speaking with, as the server needs this
									// to route traffic. all chat content should be end to end encrypted pq.
}
if ($_POST["getUserInbox"]) {
	ob_clean();
	$stmt = $mysqli->prepare("select fromusr, msgdate, body from messages where tousr = ? and keyid = ?");
	$stmt->bind_param("ss", $_SESSION["username"], $_POST["getUserInbox"]);
	$stmt->execute();
	$result = $stmt->get_result();
	$delimuse = false;
	while ($v = $result->fetch_row()) {
		if ($delimuse) {
			echo ":";
		}
		$delimuse = true;

		echo $v[0].";".$v[1].";".$v[2];
	}
	return;
}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>entQngle</title>
	</head>
	<body>
		<?php
			if (!isset($_SESSION["username"])) echo "
				<button id='signin'>Sign in</button>
				<button id='signup'>Sign up</button>
			";
		?>
		<h1>entQngle</h1>
		<i>A quantum safe chat service.</i>
		<br><br><br>

		<form id="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
			<input id="user" class="pass" type="text" name="userspeaker" placeholder="Username" value="<?php echo pqkx_encrypt($_SESSION["lastspoken"]) ?>">
			<input type="submit" value=">">
		</form>

		<br>
		<i style="color: gray;">Once read, messages are deleted permenantly.</i>
		<br><br>

		Inbox
		<div id="inbox">
			<button>a</button><br>
		</div>
	</body>
	<script>
		<?php
			if (!isset($_SESSION["username"])) echo "
				document.querySelector('#signin').onclick = () => {
					document.location.href = 'signin.php';
				};
				document.querySelector('#signup').onclick = () => {
					document.location.href = 'signup.php';
				};
			";
		?>

		document.querySelector("#user").value = pqkx_decrypt(document.querySelector("#user").value);

		document.querySelector("#form").onsubmit = () => {
			document.querySelectorAll(".pass").forEach((v) => {
				v.value = pqkx_encrypt(v.value);
			});
		};

		fetch("<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>", {
			headers: {
				"Content-Type": "application/x-www-form-urlencoded"
			},
			method: "POST",
			body: `getUserInbox=${localStorage.getItem("localpubkeyid")}`
		}).then(response => response.text())
		.then(data => {
			if (data.length == 0) {
				return;
			}

			data = data.split(";");
			let name = data[0];
			let date = data[1];
			let enctext = atob(data[2]);
			let unsharedkey1 = JSON.parse(atob(data[3].split(",")[0]));
			let unsharedkey2 = JSON.parse(atob(data[3].split(",")[1]));
			let sharedkey = finalRLWE(JSON.parse(atob(localStorage.getItem("localpubkey"))), JSON.parse(atob(localStorage.getItem("localprivkey"))), unsharedkey1, unsharedkey2);

			let key = [];
			for (let i = 0; i < keysizeRLWE / 8; i++) {
				key[i] = 0;
			}
			for (let i = 0; i < keysizeRLWE; i++) {
				key[Math.floor(i / 8)] |= sharedkey[2][i] << (i % 8);
			}

			let tmpkey = key.slice();
			let out = "";
			for (let i = 0; i < enctext.length; i++) {
				tmpkey = rollkey(tmpkey.slice());
				out += String.fromCharCode(enctext.charCodeAt(i) ^ tmpkey[tmpkey.length - 1]);
			}

			console.log(out);
		});
	</script>
</html>

<?php
require_once "localkey.php";
?>
