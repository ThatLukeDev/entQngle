<?php
require_once "pqkx.php";
?>

This should say 'Hello World!': <span id="decrypt"><?php echo pqkx_encrypt("Hello World!"); ?></span>

<script>
let obj = document.querySelector("#decrypt");
obj.innerHTML = pqkx_decrypt(obj.innerHTML);
</script>

<script src="qr.js"></script>
<script>
QR.test();
</script>
