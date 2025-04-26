<?php

require_once "rlwe.php";

// client 1
$privKey = genPrivateRLWE($privSizeRLWE, $modulusRLWE);
$pubKey = genPublicRLWE($privKey, $pubSizeRLWE, $modulusRLWE, $errorRLWE);

// client 2
$val = random_int(0, 255);
$message = encodeByteRLWE($pubKey, $samplesRLWE, $modulusRLWE, $val);
echo "Message: {$val}<br>";

// client 1
$recieved = decodeByteRLWE($privKey, $message, $modulusRLWE);
echo "Recieved: {$recieved}<br>";

?>

<input id="secret" placeholder="Input text here">

<script src="rlwe-func.js"></script>
<script>

let mat1 = randMatrix(2, 3, 4);
let mat2 = randMatrix(3, 2, 4);

document.querySelector("body").innerHTML += "<br>Mat1<br>";
document.querySelector("body").innerHTML += formatMatrix(mat1);

document.querySelector("body").innerHTML += "<br>Mat2<br>";
document.querySelector("body").innerHTML += formatMatrix(mat2);

document.querySelector("body").innerHTML += "<br>Mul<br>";
document.querySelector("body").innerHTML += formatMatrix(mulMatrix(mat1, mat2));

</script>
