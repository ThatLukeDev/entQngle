<?php

session_start();

require_once "rlwe-func.php";

if ($_POST["keysharedatarlwe"]) {
	$response = explode(",", $_POST["keysharedatarlwe"]);
	$response[0] = json_decode(base64_decode($response[0]));
	$response[1] = json_decode(base64_decode($response[1]));

	$final = finalRLWE($_SESSION["initKeyRLWE"][0], $_SESSION["initKeyRLWE"][2], $response[0], $response[1]);

	$key = [];

	for ($i = 0; $i < $GLOBALS["keysizeRLWE"]; $i++) {
		$key[intdiv($i, 8)] |= $final[$i] << ($i % 8);
	}

	$_SESSION["keyRLWE"] = $key;

	echo "SUCCESS";
}
else {
	$_SESSION["initKeyRLWE"] = initRLWE();

	echo base64_encode(json_encode($_SESSION["initKeyRLWE"][0]));
	echo ",";
	echo base64_encode(json_encode($_SESSION["initKeyRLWE"][1]));
	echo ",";
}

?>

<script>

let init = document.body.innerHTML.split(",");
init[0] = JSON.parse(atob(init[0]));
init[1] = JSON.parse(atob(init[1]));
let arr = [];
for (let key in init[1]) {
	arr[Number(key)] = init[1][key];
}
init[1] = arr;

let response = respondRLWE(init[0], init[1]);

let txtResponse = "";
txtResponse += btoa(JSON.stringify(response[0]));
txtResponse += ",";
txtResponse += btoa(JSON.stringify(response[1]));

let xhttp = new XMLHttpRequest();
xhttp.open('POST', '<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>', true);
xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
xhttp.onreadystatechange = () => {
	if (xhttp.status == 200 && xhttp.readyState == 4) {
		console.log(xhttp.response);
		let key = [];
		for (let i = 0; i < keysizeRLWE / 8; i++) {
			key[i] = 0;
		}
		for (let i = 0; i < keysizeRLWE; i++) {
			key[Math.floor(i / 8)] |= response[2][i] << (i % 8);
		}
		localStorage.setItem('key', key);
		localStorage.setItem('keydate', (new Date()).getTime());
		document.cookie = 'rlwesessionkey=true';
		window.location.href = localStorage.getItem('returnrlweshare');
	}
};
xhttp.send('keysharedatarlwe='+encodeURIComponent(txtResponse));

</script>
