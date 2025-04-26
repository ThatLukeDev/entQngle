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

let privKey = autogenPrivateRLWE();
let pubKey = autogenPublicRLWE(privKey);

let val = Math.floor(Math.random() * 2);
let encoded = encodeBitRLWE(pubKey, 16, modulusRLWE, val);
let decoded = decodeBitRLWE(privKey, encoded, modulusRLWE);

document.querySelector("body").innerHTML += `<br>Sent ${val}`;
document.querySelector("body").innerHTML += `<br>Recieved ${decoded}`;

</script>
