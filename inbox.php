<!DOCTYPE html>
<html>
	<head>
		<title>entQngle</title>
	</head>
	<body>
	</body>
	<script>
		let id = (localStorage.getItem("displayMessage"));
		let inbox = localStorage.getItem("localinbox").split(";").filter(x => x);
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

		console.log(msg);
	</script>
</html>
