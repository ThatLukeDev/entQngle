<?php

require_once "rlwe.php";

session_start();

if ($_GET["val"]) {
	$_SESSION["recieved"] = decodeByteRLWE($_SESSION["privKey"], json_decode(base64_decode($_GET["val"])), $modulusRLWE);
	echo "<non-post-start-split-header>" . $_SESSION["recieved"] ."<non-post-start-split-header>";
}
else {
	$_SESSION["privKey"] = autogenPrivateRLWE();
	$_SESSION["pubKey"] = autogenPublicRLWE($_SESSION["privKey"]);
}

?>

<span></span>

<script src="rlwe-func.js"></script>
<script> var pubKey = JSON.parse(atob("<?php echo base64_encode(json_encode($_SESSION["pubKey"])); ?>")); </script>
<script>

let val = Math.floor(Math.random() * 256);
let encoded = autoencodeByteRLWE(pubKey, val);

document.querySelector("body").innerHTML += `<br>ClientToServer: ${val}`;

let xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = () => {
	if (xhttp.readyState == 4 && xhttp.status == 200) {
		let response = xhttp.responseText.split("<non-post-start-split-header\>")[1];
		if (response) {
			document.querySelector("body").innerHTML += `<br>ServerToClient: ${response}`;
		}
		xhttp.onreadystatechange = null;
	}
};
xhttp.open("GET", "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"+`?val=${btoa(JSON.stringify(encoded))}`, true);
xhttp.send();

</script>
