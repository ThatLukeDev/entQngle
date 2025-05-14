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
	$working = 1;

	$base = $x % $mod;
	$exp = $y;
	while ($exp > 0) {
		if ($exp % 2 == 1) {
			$working = ($working * $base) % $mod;
		}
		$exp >>= 1;
		$base = ($base ** 2) % $mod;
	}

	return $working;
}

$modPowLookup = [];

function modPowCached($x, $y, $mod) { // these vals can physically be added later for speed
	if (!empty($GLOBALS["modPowLookup"][$mod." ".$x." ".$y])) {
		return $GLOBALS["modPowLookup"][$mod." ".$x." ".$y];
	}

	$out = modPow($x, $y, $mod);
	$GLOBALS["modPowLookup"][$mod." ".$x." ".$y] = $out;

	return $out;
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
	$skip = false;
	for ($i = 0; $i < count($a); $i++) {
		if ($i > 4 && $i < count($a) - 4) {
			if (!$skip) {
				echo "...&nbsp; &nbsp; ";
			}
			$skip = true;
		}
		else {
			echo "{$a[$i]} x^{$i}&nbsp; &nbsp; ";
		}
	}
	echo "<br>";
}

?>

<?php

$modulusRLWE = 25601;
$keypowRLWE = 9;
$sampleBoundRLWE = 8 / sqrt(2 * pi());

$keysizeRLWE = 2 ** $keypowRLWE;
$ringRLWE = polyAdd(polyPower([1], $keysizeRLWE), [1]);
$ring2NunityRLWE = primitive2nunity($keysizeRLWE, $modulusRLWE);

?>

<?php

function bitReverse($x, $k) {
	$mask = (1 << $k) - 1;

	$v = $x & $mask;
	$out = 0;
	for ($i = 0; $i < $k; $i++) {
		$out <<= 1;
		$out |= $v & 1;
		$v >>= 1;
	}

	return $out;
}

function basentt($in, $n2unity, $mod, $rebase) {
	$out = [];

	for ($j = 0; $j < count($in); $j++) {
		for ($i = 0; $i < count($in); $i++) {
			$out[$j] += modPowCached($n2unity, (2 * $i * $j + $i * (1 - $rebase) + $j * $rebase) % ($GLOBALS["keysizeRLWE"] * 2), $mod) * $in[$i];
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
	$out = $in;

	$rootunity = $GLOBALS["ring2NunityRLWE"];
	$k = $GLOBALS["keypowRLWE"];
	$mod = $GLOBALS["modulusRLWE"];

	$t = $GLOBALS["keysizeRLWE"];
	for ($m = 1; $m < $GLOBALS["keysizeRLWE"]; $m *= 2) {
		$t = intdiv($t, 2);
		for ($i = 0; $i < $m; $i++) {
			$j_1 = 2 * $i * $t;
			$j_2 = $j_1 + $t;
			$s = modPowCached($rootunity, bitReverse($m + $i, $k), $mod);

			for ($j = $j_1; $j < $j_2; $j++) {
				$u = $out[$j];
				$v = $out[$j + $t] * $s;
				$out[$j] = ($u + $v) % $mod;
				$out[$j + $t] = ($u - $v + $mod) % $mod;
			}
		}
	}

	$orderedOut = [];
	for ($i = 0; $i < count($out); $i++) {
		$orderedOut[bitReverse($i, $k)] = $out[$i];
	}

	return $orderedOut;
}

function inttRLWE($reverseIn) {
	$k = $GLOBALS["keypowRLWE"];
	$mod = $GLOBALS["modulusRLWE"];
	$rootunity = inverseModulus($GLOBALS["ring2NunityRLWE"], $mod);
	$inverse = inverseModulus($GLOBALS["keysizeRLWE"], $mod);

	$in = [];
	for ($i = 0; $i < count($reverseIn); $i++) {
		$in[bitReverse($i, $k)] = $reverseIn[$i];
	}

	$t = 1;
	for ($m = $GLOBALS["keysizeRLWE"]; $m > 1; $m = intdiv($m, 2)) {
		$j_1 = 0;
		$h = intdiv($m, 2);
		for ($i = 0; $i < $h; $i++) {
			$j_2 = $j_1 + $t;
			$s = modPowCached($rootunity, bitReverse($h + $i, $k), $mod);
			for ($j = $j_1; $j < $j_2; $j++) {
				$u = $in[$j];
				$v = $in[$j + $t];
				$in[$j] = ($u + $v) % $mod;
				$in[$j + $t] = (($u - $v + $mod) * $s) % $mod;
			}
			$j_1 += $t * 2;
		}
		$t *= 2;
	}
	for ($i = 0; $i < count($in); $i++) {
		$in[$i] *= $inverse;
		$in[$i] %= $mod;
		$in[$i] += $mod;
		$in[$i] %= $mod;
	}

	return $in;
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

function randomDecimal() {
	return random_int(1, 1000000000) / 1000000000;
}

function samplePolyRLWE() {
	$out = [];

	for ($i = 0; $i < $GLOBALS["keysizeRLWE"]; $i++) {
		$out[$i] = round(sqrt(-2 * log(randomDecimal())) * cos(2*pi() * randomDecimal()) * $GLOBALS["sampleBoundRLWE"]);
		$out[$i] += $GLOBALS["modulusRLWE"];
		$out[$i] %= $GLOBALS["modulusRLWE"];
	}

	return $out;
}

function SigRLWE($in) {
	$out = [];

	for ($i = 0; $i < count($in); $i++) {
		if ($in[$i] < intdiv($GLOBALS["modulusRLWE"], 4) || $in[$i] > 3 * intdiv($GLOBALS["modulusRLWE"], 4)) {
			$out[$i] = 1;
		}
		else {
			$out[$i] = 0;
		}
	}

	return $out;
}

function Mod2skRLWE($v, $w) {
	$out = polyAddRLWE($v, polyMulRLWE($w, [intdiv($GLOBALS["modulusRLWE"] - 1, 2)]));
	for ($i = 0; $i < count($out); $i++) {
		$out[$i] %= 2;
	}
	return $out;
}

function initRLWE() { // returns in the form [a, p, s]
	$a = polyRing(polyRand($GLOBALS["keysizeRLWE"], $GLOBALS["modulusRLWE"]), $GLOBALS["ringRLWE"], $GLOBALS["modulusRLWE"]);

	$s = samplePolyRLWE();
	$e = samplePolyRLWE();

	$p = polyAddRLWE(polyMulRLWE($a, $s), polyMulRLWE($e, [2]));

	return [$a, $p, $s];
}

function respondRLWE($a, $p_I) { // returns in the form [p_R, w, $key]
	$s_R = samplePolyRLWE();
	$e_R = samplePolyRLWE();

	$p_R = polyAddRLWE(polyMulRLWE($a, $s_R), polyMulRLWE($e_R, [2]));

	$e2_R = samplePolyRLWE();
	$k_R = polyAddRLWE(polyMulRLWE($p_I, $s_R), polyMulRLWE($e2_R, [2]));

	$w = SigRLWE($k_R);
	$sk_R = Mod2skRLWE($k_R, $w);

	return [$p_R, $w, $sk_R];
}

function finalRLWE($a, $s_I, $p_R, $w) {
	$e2_I = samplePolyRLWE();

	$k_I = polyAddRLWE(polyMulRLWE($p_R, $s_I), polyMulRLWE($e2_I, [2]));

	$sk_R = Mod2skRLWE($k_I, $w);

	return $sk_R;
}

?>

<br>ACC:<br><br>

<?php

/*
 * START OF TESTS
 */

polyDisplay([$GLOBALS["ring2NunityRLWE"]]);

?>

<br>JS:<br><br>

<script src="rlwe.js"></script>
<script>
polyDisplay([ring2NunityRLWE]);
</script>
