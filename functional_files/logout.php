<?php
session_start();
session_destroy();
header("Location: ../visuals/index.php");
//head to -> index.php, sends HTTP header to the browser
?>