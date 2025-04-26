<?php

require_once "rlwe.php";

session_start();

if ($_POST["pubkeyrlwe"]) {
	$pubKey = json_decode(base64_decode($_POST["pubkeyrlwe"]));

	$key = [];
	$message = [];
	for ($i = 0; $i < 32; $i++) {
		// random_int() is cryptographically secure
		$key[$i] = random_int(0, 255);
		$message[$i] = encodeByteRLWE($pubKey, $GLOBALS["samplesRLWE"], $GLOBALS["modulusRLWE"], $key[$i]);
	}
	$_SESSION["key"] = $key;

	echo base64_encode(json_encode($message));
}
else {
	echo "
		<script src='rlwe-func.js'></script>
		<script>

		let privKey = autogenPrivateRLWE();
		let pubKey = autogenPublicRLWE(privKey);
		let xhttp = new XMLHttpRequest();
		xhttp.open('POST', '" . htmlspecialchars($_SERVER["PHP_SELF"]) . "', true);
		xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xhttp.onreadystatechange = () => {
			if (xhttp.status == 202 && xhttp.readyState == 4) {
				let encoded = JSON.parse(atob(xhttp.responseText));
				let key = [];
				for (let i = 0; i < encoded.length; i++) {
					let message = autodecodeByteRLWE(privKey, encoded[i]);
					key[i] = message;
				}
				localStorage.setItem('key', key);
				localStorage.setItem('keydate', (new Date()).getTime());
				document.cookie = 'rlwesessionkey=true';
				window.location.href = localStorage.getItem('returnrlweshare');
			}
		};
		xhttp.send('pubkeyrlwe='+btoa(JSON.stringify(pubKey)));

		</script>
	";
}

?>
