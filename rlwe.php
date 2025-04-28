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
		$out[$i] = $a[$i] - $b[$i];
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

function polyPower($a, $b) {
	$out = [];

	for ($i = 0; $i < $b; $i++) {
		$out[$i] = 0;
	}

	for ($i = 0; $i < count($a); $i++) {
		$out[count($out)] = $a[$i];
	}

	return $out;
}

function polyDegree($a) {
	return count(polyTrim($a)) - 1;
}

function polyMod($eqn, $div) {
	$equation = polyTrim($eqn);
	$divisor = polyTrim($div);
	$divisorDegree = polyDegree($divisor);
	$divisorSignificant = $divisor[$divisorDegree];

	$modulus = $equation;

	for ($difference = polyDegree($equation) - $divisorDegree; $difference >= 0; $difference--) {
		$shortDivisor = polyPower($divisor, $difference);
		$shortQuotient = $modulus[$difference + $divisorDegree] / $divisorSignificant;

		$modulus = polySub($modulus, polyMul($shortDivisor, [$shortQuotient]));
	}

	return polyTrim($modulus);
}

?>

<?php

function polyDisplay($a) {
	for ($i = 0; $i < count($a); $i++) {
		echo "{$a[$i]} x^{$i}&nbsp; &nbsp; ";
	}
	echo "<br>";
}

?>

<?php

polyDisplay(polyMod([1, 2, 1], [1, 1]));
polyDisplay(polyMod([-4, 0, -2, 1], [-3, 1]));

?>
