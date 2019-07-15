<?php

session_start();

unset($_SESSION['email']);
setcookie('token', '', time() - 3600);

header('Location: login.php');
exit;