<?php

function randMatrix($row, $col, $max) {
	$out = [[]];

	for ($i = 0; $i < $row; $i++) {
		for ($j = 0; $j < $col; $j++) {
			// random_int() is cryptographically secure
			$out[$i][$j] = random_int(0, $max);
		}
	}

	return $out;
}

function scaleMatrix($mat, $scalar) {
	for ($i = 0; $i < count($mat, 0); $i++) {
		for ($j = 0; $j < count($mat[$i], 0); $j++) {
			$mat[$i][$j] *= $scalar;
		}
	}

	return $mat;
}

function mulMatrix($a, $b) {
	$out = [[]];

	for ($i = 0; $i < count($a, 0); $i++) {
		for ($j = 0; $j < count($b[0], 0); $j++) {
			for ($k = 0; $k < count($b); $k++) {
				$out[$i][$j] += $a[$i][$k] * $b[$k][$j];
			}
		}
	}

	return $out;
}

?>

<?php

function formatMatrix($mat) {
	$out = "";

	for ($i = 0; $i < count($mat, 0); $i++) {
		for ($j = 0; $j < count($mat[$i], 0); $j++) {
			$out .= str_replace(" ", "&nbsp;", str_pad(strval($mat[$i][$j]), 15));
		}
		$out .= "<br><br><br>";
	}

	return $out;
}

function formatVector($vec) {
	$out = "";

	for ($i = 0; $i < count($vec, 0); $i++) {
		$out .= str_replace(" ", "&nbsp;", str_pad(strval($vec[$i]), 15));
	}

	return $out;
}

?>

<?php

function randInstruction() {
	// mitigate timing-based attacks
	$sum = 0;
	for ($i = 0; $i < random_int(0, 16); $i++) {
		$sum += $i;
	}
}

function genPrivateRLWE($size, $mod) {
	return randMatrix($size, 1, $mod - 1);
}

function genPublicRLWE($key, $size, $mod, $error) {
	$key1 = randMatrix($size, count($key), $mod - 1);
	$key2 = mulMatrix($key1, $key);
	$keyWithErrors = [];
	for ($i = 0; $i < $size; $i++) {
		$keyWithErrors[$i] = $key2[$i][0];

		// random_int() is cryptographically secure
		$keyWithErrors[$i] += random_int(-$error, $error);

		$keyWithErrors[$i] += $mod;
		$keyWithErrors[$i] %= $mod;
	}

	randInstruction();

	return [ $key1, $keyWithErrors ];
}

function mixPublicRLWE($key, $samples, $mod) {
	$out1 = [[]];
	$out2 = 0;

	for ($t = 0; $t < $samples; $t++) {
		// random_int() is cryptographically secure
		$i = random_int(0, count($key[0], 0));
		for ($j = 0; $j < count($key[0][0], 0); $j++) {
			$out1[0][$j] += $key[0][$i][$j];
			$out1[0][$j] %= $mod;
		}
		$out2 += $key[1][$i];
		$out2 %= $mod;
	}

	randInstruction();

	return [ $out1, $out2 ];
}

function encodeBitRLWE($key, $samples, $mod, $bit) {
	$mixed = mixPublicRLWE($key, $samples, $mod);

	if ($bit == 1) {
		$mixed[1] += intdiv($mod, 2);
		$mixed[1] %= $mod;
	}
	else  {
		// helps further with timing based attacks
		$extra += intdiv($mod, 2);
		$extra %= $mod;
	}

	randInstruction();

	return $mixed;
}

function decodeBitRLWE($key, $msg, $mod) {
	$difference = mulMatrix($msg[0], $key)[0][0] - $msg[1];

	$difference += intdiv($mod, 4);
	$difference %= $mod;

	$val = 0;
	if ($difference > intdiv($mod, 2)) {
		$val = 1;
	}

	return $val;
}

?>

<?php

function encodeByteRLWE($key, $samples, $mod, $msg) {
	$out = [];

	for ($i = 0; $i < 8; $i++) {
		$bit = ($msg & (1 << $i)) >> $i;
		$out[$i] = encodeBitRLWE($key, $samples, $mod, $bit);
	}

	return $out;
}

function decodeByteRLWE($key, $msg, $mod) {
	$out = 0;

	for ($i = 0; $i < 8; $i++) {
		$out |= decodeBitRLWE($key, $msg[$i], $mod) << $i;
	}

	return $out;
}

function encodeStrRLWE($key, $samples, $mod, $str) {
	$out = [];

	for ($i = 0; $i < strlen($str); $i++) {
		$out[i] = encodeByteRLWE($key, $samples, $mod, ord($str[i]));
	}

	return $out;
}

function decodeStrRLWE($key, $msg, $mod) {
	$out = "";

	for ($i = 0; $i < count($msg, 0); $i++) {
		$out .= chr(decodeByteRLWE($key, $msg[i], $mod));
	}

	return $out;
}

?>

<?php

$modulusRLWE = 524287;
$privSizeRLWE = 16;
$pubSizeRLWE = 128;
$errorRLWE = 8191;
$samplesRLWE = 16;

function autogenPrivateRLWE() {
	return genPrivateRLWE($GLOBALS["privSizeRLWE"], $GLOBALS["modulusRLWE"]);
}
function autogenPublicRLWE($privKey) {
	return genPublicRLWE($privKey, $GLOBALS["pubSizeRLWE"], $GLOBALS["modulusRLWE"], $GLOBALS["errorRLWE"]);
}
function autoencodeStrRLWE($pubKey, $str) {
	return encodeStrRLWE($pubKey, $GLOBALS["samplesRLWE"], $GLOBALS["modulusRLWE"], $str);
}
function autodecodeStrRLWE($privKey, $msg) {
	return decodeStrRLWE($privKey, $msg, $GLOBALS["modulusRLWE"]);
}

?>

<?php

/*
 * QKS
 *
 * Example usage:
 *
 * // client 1
 * $privKey = genPrivateRLWE($privSizeRLWE, $modulusRLWE);
 * $pubKey = genPublicRLWE($privKey, $pubSizeRLWE, $modulusRLWE, $errorRLWE);
 * 
 * // client 2
 * $val = random_int(0, 255);
 * $message = encodeByteRLWE($pubKey, $samplesRLWE, $modulusRLWE, $val);
 * echo "Message: {$val}<br>";
 * 
 * // client 1
 * $recieved = decodeByteRLWE($privKey, $message, $modulusRLWE);
 * echo "Recieved: {$recieved}<br>";
 *
 */

?>
