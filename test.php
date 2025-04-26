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
		for ($j = 0; $j < count($b[$i], 0); $j++) {
			for ($k = 0; $k < count($b); $k++) {
				$out[$i][$j] += $a[$i][$k] * $b[$k][$j];
			}
		}
	}

	return $out;
}
?>

<?php
$mat1 = randMatrix(2, 2, 9);
$mat2 = randMatrix(2, 2, 9);

echo formatMatrix($mat1);
echo formatMatrix($mat2);

echo formatMatrix(mulMatrix($mat1, $mat2));
?>
