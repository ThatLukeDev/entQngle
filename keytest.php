<?php

require_once "rlwe.php";

session_start();

// any post handling should be done here

autosessionRLWE();

?>
<div id="recieve">client recieved: </div>
last recieved (1 behind): <?php echo base64_encode(join(array_map("chr", $_SESSION["key"]))); ?>
<script>
document.querySelector("#recieve").innerHTML += btoa(String.fromCharCode.apply(null, new Uint8Array(key)));
</script>
<br>
This should say 'Hello World!': <span id="decrypt"><?php echo rlwe_encrypt("Hello World!"); ?></span>
<script>
let obj = document.querySelector("#decrypt");
obj.innerHTML = rlwe_decrypt(obj.innerHTML);
</script>
