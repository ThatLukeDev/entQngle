<?php

/*
 * WARNING: localStorage is used here as the securecookies by the webpage relies on TLS, which is not quantum-safe.
 * Any XSS or local script can read localStorage, thus breaking the quantum-safe algorithm, so dont get hacked!
 * To get around this, keys are reset every hour.
 */

$keyExpireAutoRLWE = 60 * 60 * 1000;

session_start();

echo '<script>
	if (((new Date()).getTime() - parseInt(localStorage.getItem("keydate")) > ' . $GLOBALS["keyExpireAutoRLWE"] . ') || !document.cookie.match(/lwesessionkey/)) {
		localStorage.setItem("key", null);
		localStorage.setItem("keydate", null);
	}
	if (localStorage.getItem("key") == "null") {
		localStorage.setItem("returnrlweshare", "' . htmlspecialchars($_SERVER["PHP_SELF"]) . '");
		window.location.href = "rlwe.php";
	}
</script>';

?>

<?php

function rollkey($acckey) {
	$key = $acckey;

	$len = count($key, 0);

	$hash = 0;
	for ($i = 0; $i < $len - 1; $i++) {
		$hash += $key[$i];
		$key[$i] = $key[$i + 1];
	}
	$key[$len - 1] = $hash % 256;

	return $key;
}
function enc_cbc($str) { // works once keysharing has occured, basic cbc xor
	if (!isset($_SESSION["keyRLWE"])) {
		return "ERR_PQKX";
	}
	$key = $_SESSION["keyRLWE"];
	$out = "";

	for ($i = 0; $i < strlen($str); $i++) {
		$key = rollkey($key);
		$out .= chr(ord($str[$i]) ^ $key[count($key, 0) - 1]);
	}

	return $out;
}
function pqkx_encrypt($str) {
	return base64_encode(enc_cbc($str));
}
function pqkx_decrypt($str) {
	return enc_cbc(base64_decode($str));
}

?>

<script>
var rollkey = (rollingKey) => {
	let hash = 0;
	for (let i = 0; i < rollingKey.length - 1; i++) {
		hash += parseInt(rollingKey[i]);
		rollingKey[i] = rollingKey[i + 1];
	}
	rollingKey[rollingKey.length - 1] = hash % 256;

	return rollingKey;
};
var enc_cbc = (str) => {
	if (localStorage.getItem("key") == null) {
		return "PQKX_ERR";
	}
	let tmpkey = localStorage.getItem("key").split(",").slice();
	let out = "";
	for (let i = 0; i < str.length; i++) {
		tmpkey = rollkey(tmpkey.slice());
		out += String.fromCharCode(str.charCodeAt(i) ^ tmpkey[tmpkey.length - 1]);
	}

	return out;
};
var pqkx_encrypt = (str) => {
	return btoa(enc_cbc(str));
};
var pqkx_decrypt = (str) => {
	return enc_cbc(atob(str));
};
</script>
