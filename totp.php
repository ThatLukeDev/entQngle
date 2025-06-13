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
	echo bin2hex($mac) . "<br>";
	$offset = ord($mac[19]) & 0xf;
	return	  ((ord($mac[$offset]) & 0x7f) << 24
		|  (ord($mac[$offset + 1])) << 16
		|  (ord($mac[$offset + 2])) << 8
		|  (ord($mac[$offset + 3]))) % 100000000;
}

function totp($key, $t) {
	$btime = "";

	$btime = 1;//intdiv(time(), $t);
	$stime = "";
	for ($i = 8; $i >= 0; $i--) {
		$stime .= chr(($btime >> ($i * 8)) & 0xff);
	}

	return hotp_6($key, $stime);
}

echo hotp_6("12345678901234567890", "\0\0\0\0\0\0\0\1");

?>
