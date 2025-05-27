<?php
session_start();
session_destroy();
header('Location: /website/login.php');
exit;
?>