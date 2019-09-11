<?php

session_start();

unset($_SESSION['email']);
unset($_SESSION['csrf']);
setcookie('token', '', time() - 3600);

header('Location: login.php');
exit;