<?php

function polyAdd($a, $b) {
	$out = [];
	$len = max(count($a), count($b));
	for ($i = 0; $i < $len; $i++) {
		$out[$i] = $a[$i] + $b[$i];
	}

	return $out;
}

function polySub($a, $b) {
	$out = [];
	$len = max(count($a), count($b));
	for ($i = 0; $i < $len; $i++) {
		$out[$i] = $a[$i] + $b[$i];
	}

	return $out;
}

function polyMul($a, $b) {
	$out = [];

	for ($i = 0; $i < count($a); $i++) {
		for ($j = 0; $j < count($b); $j++) {
			$out[$i + $j] += $a[$i] * $b[$j];
		}
	}

	return $out;
}

function polyTrim($a) {
	$out = [];
	$last = 0;

	for ($i = 0; $i < count($a); $i++) {
		if ($a[$i] != 0) {
			$last = $i + 1;
		}
	}

	for ($i = 0; $i < $last; $i++) {
		$out[$i] = $a[$i];
	}

	return $out;
}

?>

<?php

function polyDisplay($a) {
	for ($i = 0; $i < count($a); $i++) {
		echo "{$a[$i]} x^{$i}&nbsp; &nbsp; ";
	}
}

?>

<?php

polyDisplay(polyTrim([1, 2, 4, 0, 2, 0, 0]));

?>
