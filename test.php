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
	$key2 = array_merge(mulMatrix($key1, $key));
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

$modulus = 524287;
$privSize = 16;
$pubSize = 128;
$error = 8191;
$samples = 16;

// client 1
$privKey = genPrivateRLWE($privSize, $modulus);
$pubKey = genPublicRLWE($privKey, $pubSize, $modulus, $error);

// client 2
$val = random_int(0, 1);
$message = encodeBitRLWE($pubKey, $samples, $modulus, $val);
echo "Message: {$val}<br>";

// client 1
$recieved = decodeBitRLWE($privKey, $message, $modulus);
echo "Recieved: {$recieved}<br>";

?>
