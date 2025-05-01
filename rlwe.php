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

function modPow($x, $y, $mod) {
	$working = $x;

	for ($i = 1; $i < $y; $i++) {
		$working *= $x;
		$working %= $mod;
	}

	return $working;
}

function primitivenunity($n, $mod) {
	for ($root = 0; $root < $mod; $root++) {
		if (modPow($root, $n, $mod) == 1) {
			$taken = false;
			for ($k = 1; $k < $n; $k++) {
				if (modPow($root, $k, $mod) == 1) {
					$taken = true;
				}
			}
			if (!$taken) {
				return $root;
			}
		}
	}
	return -1;
}

function primitive2nunity($n, $mod) {
	$nthunity = primitivenunity($n, $mod);
	for ($root = 0; $root < $mod; $root++) {
		if (modPow($root, 2, $mod) == $nthunity && modPow($root, $n, $mod) == $mod - 1) {
			return $root;
		}
	}
	return -1;
}

function ntt() {
}

function polyRand($n, $max) {
	$out = [];

	for ($i = 0; $i < $n; $i++) {
		$out[$i] = random_int(-$max, $max);
	}

	return $out;
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

$modulusRLWE = 25601;
$keysizeRLWE = 512;
$sampleBoundRLWE = 5;
$ringRLWE = polyAdd(polyPower([1], $keysizeRLWE), [1]);

?>

<?php

function samplePolyRLWE() {
	$out =  polyRand($GLOBALS["keysizeRLWE"], $GLOBALS["sampleBoundRLWE"]);

	for ($i = 0; $i < count($out); $i++) {
		$out[$i] = ($out[$i] + $GLOBALS["modulusRLWE"]) % $GLOBALS["modulusRLWE"];
	}

	return $out;
}

function initRLWE() { // returns in the form [a, p, s, e]
	$a = polyMod(polyRand($GLOBALS["keysizeRLWE"], $GLOBALS["modulusRLWE"]), $GLOBALS["ringRLWE"]);

	$s = samplePolyRLWE();
	$e = samplePolyRLWE();

	$p = polyMod(polyAdd(polyMul($a, $s), polyMul($e, [2])), $GLOBALS["ringRLWE"]);

	return [$a, $p, $s, $e];
}

?>

<?php

echo primitivenunity(4, 7681);
echo "<br>";
echo primitive2nunity(4, 7681);

/*
$init = initRLWE();

polyDisplay($init[0]);
echo "<br><br>";
polyDisplay($init[1]);
echo "<br><br>";
polyDisplay($init[2]);
echo "<br><br>";
polyDisplay($init[3]);
echo "<br><br>";
 */

?>
