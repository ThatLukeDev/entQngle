<?php

require_once "rlwe.php";

session_start();

autosessionRLWE();

?>

last recieved <?php var_dump($_SESSION["key"]); ?>
