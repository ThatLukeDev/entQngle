<?php

require_once "rlwe.php";

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if ($_GET["val"]) {
		$_SESSION["recieved"] = decodeByteRLWE($_SESSION["privKey"], json_decode(base64_decode($_GET["val"])), $modulusRLWE);
		echo $_SESSION["recieved"];
	}
	if ($_GET["vals"]) {
		$_SESSION["recieved"] = autodecodeStrRLWE($_SESSION["privKey"], json_decode(base64_decode($_GET["val"])));
		echo $_SESSION["recieved"];
	}
	echo "<non-post-start-split-header>";
}
else {
	$_SESSION["privKey"] = autogenPrivateRLWE();
	$_SESSION["pubKey"] = autogenPublicRLWE($_SESSION["privKey"]);
}

?>

<input id="secret" placeholder="Input text here">
<button id="secretSubmit">Send (one way test)</button>

<script src="rlwe-func.js"></script>
<script> var pubKey = JSON.parse(atob("<?php echo base64_encode(json_encode($_SESSION["pubKey"])); ?>")); </script>
<script>

let val = Math.floor(Math.random() * 256);
let encoded = autoencodeByteRLWE(pubKey, val);

document.querySelector("body").innerHTML += `<br>1: ${val}`;

document.querySelector("#secretSubmit").onclick = () => {
	let encodedStr = autoencodeStrRLWE(pubKey, document.querySelector("#secret").value);
	let xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = () => {
		let response = xhttp.responseText.split("<non-post-start-split-header\>")[0];
		if (response) {
			document.querySelector("body").innerHTML += `<br>2: ${response}`;
		}
		let text = xhttp.responseText.split("<non-post-start-split-header\>")[1];
		if (xhttp.responseText.split("<non-post-start-split-header\>")[2] != null) {
			document.querySelector("body").innerHTML += `<br>2: ${text}`;
		}
	};
	xhttp.open("POST", "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"+`?vals=${btoa(JSON.stringify(encodedStr))}`, true);
	xhttp.send();
};

let xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = () => {
	let response = xhttp.responseText.split("<non-post-start-split-header\>")[0];
	if (response) {
		document.querySelector("body").innerHTML += `<br>2: ${response}`;
	}
	let text = xhttp.responseText.split("<non-post-start-split-header\>")[1];
	if (xhttp.responseText.split("<non-post-start-split-header\>")[2] != null) {
		document.querySelector("body").innerHTML += `<br>2: ${text}`;
	}
};
xhttp.open("POST", "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"+`?val=${btoa(JSON.stringify(encoded))}`, true);
xhttp.send();

</script>
