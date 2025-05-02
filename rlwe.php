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

function polyRing($eqn, $div, $mod) {
	$out = $eqn;
	for ($i = 0; $i < count($out); $i++) {
		$out[$i] += $mod;
		$out[$i] %= $mod;
	}
	return polyMod($out, $div);
}

function modPow($x, $y, $mod) {
	if ($y == 0) {
		return 1;
	}
	$working = $x;

	for ($i = 1; $i < $y; $i++) {
		$working *= $x;
		$working %= $mod;
	}

	return $working;
}

function modPowScale($x, $y, $mod) {
	if ($y == 0) {
		return 1;
	}
	$working = 1;

	$base = $x % $mod;
	$exp = $y;
	while ($exp > 0) {
		if ($i & 1 == 1) {
			$working *= $base;
			$working %= $mod;
		}
		$exp >>= 1;
		$base *= $base;
		$base %= $mod;
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

$modulusRLWE = 7681;//25601;
$keysizeRLWE = 4;//512;
$sampleBoundRLWE = 5;
$ringRLWE = polyAdd(polyPower([1], $keysizeRLWE), [1]);
$ring2NunityRLWE = primitive2nunity($keysizeRLWE, $modulusRLWE);

?>

<?php

function basentt($in, $n2unity, $mod, $rebase) {
	$out = [];

	for ($j = 0; $j < count($in); $j++) {
		for ($i = 0; $i < count($in); $i++) {
			$out[$j] += modPowScale($n2unity, (2 * $i * $j + $i * (1 - $rebase) + $j * $rebase) % $GLOBALS["keysizeRLWE"], $mod) * $in[$i];
			$out[$j] %= $mod;
		}
	}

	return $out;
}

function inverseModulus($val, $mod) {
	for ($i = 1; $i < $mod; $i++) {
		if (($i * $val) % $mod == 1) {
			return $i;
		}
	}
	return -1;
}

function nttRLWE($in) {
	return basentt($in, $GLOBALS["ring2NunityRLWE"], $GLOBALS["modulusRLWE"], 0);
}

function inttRLWE($in) {
	$out = basentt($in, inverseModulus($GLOBALS["ring2NunityRLWE"], $GLOBALS["modulusRLWE"]), $GLOBALS["modulusRLWE"], 1);
	$inverse = inverseModulus($GLOBALS["keysizeRLWE"], $GLOBALS["modulusRLWE"]);
	for ($i = 0; $i < count($out); $i++) {
		$out[$i] *= $inverse;
		$out[$i] %= $GLOBALS["modulusRLWE"];
	}
	return $out;
}

function polyMulRLWE($a, $b) {
	for ($i = count($a); $i < $GLOBALS["keysizeRLWE"]; $i++) {
		$a[$i] = 0;
	}
	for ($i = count($b); $i < $GLOBALS["keysizeRLWE"]; $i++) {
		$b[$i] = 0;
	}
	$antt = nttRLWE($a);
	$bntt = nttRLWE($b);

	$outntt = [];
	for ($i = 0; $i < count($a); $i++) {
		$outntt[$i] = $antt[$i] * $bntt[$i];
	}

	return inttRLWE($outntt);
}

function polyAddRLWE($a, $b) {
	for ($i = count($a); $i < $GLOBALS["keysizeRLWE"]; $i++) {
		$a[$i] = 0;
	}
	for ($i = count($b); $i < $GLOBALS["keysizeRLWE"]; $i++) {
		$b[$i] = 0;
	}
	$antt = nttRLWE($a);
	$bntt = nttRLWE($b);

	$outntt = [];
	for ($i = 0; $i < count($a); $i++) {
		$outntt[$i] = $antt[$i] + $bntt[$i];
	}

	return inttRLWE($outntt);
}

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
	$a = polyRing(polyRand($GLOBALS["keysizeRLWE"], $GLOBALS["modulusRLWE"]), $GLOBALS["ringRLWE"], $GLOBALS["modulusRLWE"]);

	$s = samplePolyRLWE();
	$e = samplePolyRLWE();

	$p = polyAddRLWE(polyMulRLWE($a, $s), polyMulRLWE($e, [2]));

	return [$a, $p, $s, $e];
}

?>

<?php

$init = initRLWE();

polyDisplay($init[0]);
echo "<br><br>";
polyDisplay($init[1]);
echo "<br><br>";
polyDisplay($init[2]);
echo "<br><br>";
polyDisplay($init[3]);
echo "<br><br>";

?>
