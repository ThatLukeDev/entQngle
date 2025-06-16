<?php

function hmac_sha1($key, $text) { // key cannot be larger than size
	$size = 512 / 8;
	$ipad = "";
	$opad = "";
	for ($i = 0; $i < $size; $i++) {
		$ipad .= "\x36";
		$opad .= "\x5c";
	}

	for ($i = strlen($key); $i < $size; $i++) {
		$key .= "\0";
	}

	return sha1(($key ^ $opad) . sha1(($key ^ $ipad) . $text, true), true); // yes, xor works on strings
}

function hotp_6($key, $text) {
	$mac = substr(hmac_sha1($key, $text), 0, 20);
	$offset = ord($mac[19]) & 0xf;
	return	  ((ord($mac[$offset]) & 0x7f) << 24
		|  (ord($mac[$offset + 1])) << 16
		|  (ord($mac[$offset + 2])) << 8
		|  (ord($mac[$offset + 3]))) % 100000000;
}

function totp($key) {
	$btime = "";

	$btime = intdiv(time(), 30);
	$stime = "";
	for ($i = 7; $i >= 0; $i--) {
		$stime .= chr(($btime >> ($i * 8)) & 0xff);
	}

	return hotp_6($key, $stime);
}

$b32char = [
	'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
	'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
	'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X',
	'Y', 'Z', '2', '3', '4', '5', '6', '7',
];

function genotpauth($user, $key) {
	$secret = "";

	for ($i = 5; $i < strlen($key) * 8 + 5; $i += 5) {
		$val = 0;
		$val |= ord($key[intdiv($i, 8)]) >> (8 - ($i % 8));
		$val |= ord($key[intdiv($i - 5, 8)]) << ($i % 8);
		$val &= 31;

		$secret .= $GLOBALS["b32char"][$val];
	}

	return "otpauth://totp/entQngle:" . $user . "?digits=8&secret=" . $secret;
}

echo genotpauth("test", "foobar");

?>
