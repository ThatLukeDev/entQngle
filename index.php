<?php
require_once "lwe.php";
session_start();
autosessionLWE();
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
	</script>
</html>
