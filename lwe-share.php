<?php

require_once "lwe.php";

session_start();

if ($_POST["pubkeylwe"]) {
	$pubKey = json_decode(base64_decode($_POST["pubkeylwe"]));

	$key = [];
	$message = [];
	for ($i = 0; $i < 32; $i++) {
		// random_int() is cryptographically secure
		$key[$i] = random_int(0, 255);
		$message[$i] = encodeByteLWE($pubKey, $GLOBALS["samplesLWE"], $GLOBALS["modulusLWE"], $key[$i]);
	}
	$_SESSION["key"] = $key;

	echo base64_encode(json_encode($message));
}
else {
	echo "
		<script src='lwe-func.js'></script>
		<script>

		let privKey = autogenPrivateLWE();
		let pubKey = autogenPublicLWE(privKey);
		let xhttp = new XMLHttpRequest();
		xhttp.open('POST', '" . htmlspecialchars($_SERVER["PHP_SELF"]) . "', true);
		xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xhttp.onreadystatechange = () => {
			if (xhttp.status == 200 && xhttp.readyState == 4) {
				let encoded = JSON.parse(atob(xhttp.responseText));
				let key = [];
				for (let i = 0; i < encoded.length; i++) {
					let message = autodecodeByteLWE(privKey, encoded[i]);
					key[i] = message;
				}
				localStorage.setItem('key', key);
				localStorage.setItem('keydate', (new Date()).getTime());
				document.cookie = 'lwesessionkey=true';
				window.location.href = localStorage.getItem('returnlweshare');
			}
		};
		xhttp.send('pubkeylwe='+btoa(JSON.stringify(pubKey)));

		</script>
	";
}

?>
