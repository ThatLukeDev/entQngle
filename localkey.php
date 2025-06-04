<?php
require_once "config.php";

session_start();

require_once "rlwe-func.php";

$keyvkwarning = "";

if ($_POST["localkeyrlwe"]) {
	if (!isset($_SESSION["username"])) {
		echo "Not signed in";
		return;
	}
	$stmt = $mysqli->prepare("select keyid from userkeys where username = ?");
	$stmt->bind_param("s", $_SESSION["username"]);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result->fetch_row()[0] != $_POST["lastidrlwe"]) {
		$keyvkwarning = "User account E2E key changed. Messages are now only availible from this device";
	}
	$stmt = $mysqli->prepare("replace into userkeys VALUES (?, ?, ?)");
	$stmt->bind_param("sss", $_SESSION["username"], $_POST["localkeyrlwe"], $_POST["localidrlwe"]);
	$stmt->execute();
	return;
}

?>

<script>

let lastpubkeyid = localStorage.getItem("localpubkeyid");

let pubkeyid = "";
let vals = new Uint32Array(24);
self.crypto.getRandomValues(vals); 	// its just an id, doesnt need security, but why not
					// if this fails, it is non critical
for (let i = 0; i < 24; i++) {
	pubkeyid += vals[i].toString();
}
localStorage.setItem("localpubkeyid", pubkeyid);

let localpubkey = initRLWE();

let txtpubkey = `${btoa(JSON.stringify(localpubkey[0]))},${btoa(JSON.stringify(localpubkey[1]))},`;

let xhttp = new XMLHttpRequest();
xhttp.open('POST', '<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>', true);
xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
xhttp.onreadystatechange = () => {
	if (xhttp.status == 200 && xhttp.readyState == 4) {
	}
};
xhttp.send(`localkeyrlwe=${txtpubkey}&lastidrlwe=${lastpubkeyid}&localidrlwe=${pubkeyid}`);

</script>
