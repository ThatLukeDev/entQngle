<?php

require_once "rlwe.php";

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$_SESSION["recieved"] = decodeByteRLWE($_SESSION["privKey"], json_decode(base64_decode($_GET["val"])), $modulusRLWE);
	echo $_SESSION["recieved"];
	echo "<non-post-start-split-header>";
}
else {
	$_SESSION["privKey"] = genPrivateRLWE($privSizeRLWE, $modulusRLWE);
	$_SESSION["pubKey"] = genPublicRLWE($_SESSION["privKey"], $pubSizeRLWE, $modulusRLWE, $errorRLWE);
}

?>

<input id="secret" placeholder="Input text here">

<script src="rlwe-func.js"></script>
<script> var pubKey = JSON.parse(atob("<?php echo base64_encode(json_encode($_SESSION["pubKey"])); ?>")); </script>
<script>

let val = Math.floor(Math.random() * 256);
let encoded = autoencodeByteRLWE(pubKey, val);

document.querySelector("body").innerHTML += `<br>1: ${val}`;

let xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = () => {
	let response = xhttp.responseText.split("<non-post-start-split-header>")[0];
	if (response) {
		document.querySelector("body").innerHTML += `<br>2: ${response}`;
	}
};
xhttp.open("POST", "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"+`?val=${btoa(JSON.stringify(encoded))}`, true);
xhttp.send();

</script>
