<?php
require_once "lwe.php";
session_start();
autosessionLWE();
?>

This should say 'Hello World!': <span id="decrypt"><?php echo lwe_encrypt("Hello World!"); ?></span>

<script>
let obj = document.querySelector("#decrypt");
obj.innerHTML = lwe_decrypt(obj.innerHTML);
</script>
