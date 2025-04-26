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

	return [ $key1, $keyWithErrors ];
}

?>

<?php

$modulus = 10;
$privSize = 2;
$pubSize = 3;
$error = 1;

$privKey = genPrivateRLWE($privSize, $modulus);
$pubKey = genPublicRLWE($privKey, $pubSize, $modulus, $error);

echo "Private:<br>";
echo formatMatrix($privKey);
echo "Public:<br>";
echo formatMatrix($pubKey[0]);
echo "Untrue:<br>";
echo formatVector($pubKey[1]);

?>
