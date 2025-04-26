<?php

require_once "rlwe.php";

session_start();

// any post handling should be done here

autosessionRLWE();

?>
last recieved (1 behind): <?php echo base64_encode(join(array_map("chr", $_SESSION["key"]))); ?>
<div id="recieve">client recieved: </div>
<script>
document.querySelector("#recieve").innerHTML += btoa(String.fromCharCode.apply(null, new Uint8Array(key)));
</script>
