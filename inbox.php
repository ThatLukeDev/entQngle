<?php

require_once "pqkx.php";

?>

<!DOCTYPE html>
<html>
	<head>
		<title>entQngle</title>
	</head>
	<body>
		<button id="homeBtn">Home</button>
		<button id="replyBtn">Reply</button>
		<button id="replydelBtn">Reply & Delete</button>
		<button id="deleteBtn">Delete</button>
		<br><br>
		<div id="host"></div>
	</body>
	<script>
		let username = pqkx_decrypt("<?php echo pqkx_encrypt($_SESSION["username"]); ?>");

		let id = localStorage.getItem("displayMessage");
		let inbox = localStorage.getItem(`localinbox${username}`).split(";").filter(x => x);
		let msg = "";
		for (let i = 0; i < inbox.length; i++) {
			if (atob(inbox[i].split(":")[3]) == id) {
				msg = inbox[i];
			}
		}
		msg = msg.split(":");
		if (msg == "") {
			throw new Error();
		}
		for (let j = 0; j < msg.length; j++) {
			msg[j] = atob(msg[j]);
		}

		document.querySelector("#host").textContent = `From: ${msg[1]}\nDate: ${msg[0]}\n--------------------------------------------------\n${msg[2]}`;

		document.querySelector("#homeBtn").onclick = () => {
			window.location.href = "index.php";
		};
		document.querySelector("#replyBtn").onclick = () => {
			window.location.href = "chat.php?user=<?php echo pqkx_encrypt($_SESSION["username"]); ?>";
		};
		document.querySelector("#deleteBtn").onclick = () => {
			for (let i = 0; i < inbox.length; i++) {
				if (atob(inbox[i].split(":")[3]) == id) {
					inbox.splice(i, 1);
				}
			}
			localStorage.setItem(`localinbox${username}`, inbox.join(";"));
			document.querySelector("#homeBtn").click();
		};
		document.querySelector("#replydelBtn").onclick = () => {
			document.querySelector("#deleteBtn").click();
			document.querySelector("#replyBtn").click();
		};
	</script>
	<style>
		#host {
			white-space: pre-line;
		}
	</style>
</html>
