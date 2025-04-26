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

	for ($i = 0; $i < count($mat); $i++) {
		for ($j = 0; $j < count($mat[i]); $j++) {
			$out .= $mat[i][j];
		}
		$out .= "\n";
	}

	return $out;
}

$mat = randMatrix(3, 4, 9);
echo formatMatrix($mat);
?>
