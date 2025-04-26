<?php

require_once "rlwe.php";

session_start();

// any post handling should be done here

autosessionRLWE();

?>

This should say 'Hello World!': <span id="decrypt"><?php echo rlwe_encrypt("Hello World!"); ?></span>
<script>
let obj = document.querySelector("#decrypt");
obj.innerHTML = rlwe_decrypt(obj.innerHTML);
</script>
