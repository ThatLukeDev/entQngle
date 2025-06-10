<!DOCTYPE html>
<html>
	<head>
		<title>entQngle</title>
	</head>
	<body>
	</body>
	<script>
		let id = Number(localStorage.getItem("displayMessage"));
		let msg = localStorage.getItem("localinbox").split(";").filter(x => x)[id].split(":");
		if (msg == "") {
			throw new Error();
		}
		for (let j = 0; j < msg.length; j++) {
			msg[j] = atob(msg[j]);
		}

		console.log(msg);
	</script>
</html>
