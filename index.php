<?php

require_once "pqkx.php";
session_start();

if ($_POST["userspeaker"]) {
	$_SESSION["lastspoken"] = pqkx_decrypt($_POST["userspeaker"]);
	header("Location: chat.php?user=" . $_SESSION["lastspoken"]);	// you can see who you are speaking with as an external viewer.
									// all traffic from the webpage should be treated as compromised,
									// this includes who you are speaking with, as the server needs this
									// to route traffic. all chat content should be end to end encrypted pq.
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
			<input type="submit" value="Chat">
		</form>
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
	</script>
</html>
