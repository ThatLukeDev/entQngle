<!DOCTYPE html>
<html>
	<head>
		<title>entQngle</title>
	</head>
	<body>
		<?php
			if (session_status() != PHP_SESSION_ACTIVE) echo "
				<button id='signin'>Sign in</button>
				<button id='signup'>Sign up</button>
			";
		?>
	</body>
	<script>
		document.querySelector("#signin").onclick = () => {
			document.location.href = "signin.php";
		};
		document.querySelector("#signup").onclick = () => {
			document.location.href = "signup.php";
		};
	</script>
</html>
