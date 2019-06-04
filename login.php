<?php

use google\appengine\api\users\UserService;

$url = UserService::createLoginURL($_SERVER['REQUEST_URI']);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdn.metroui.org.ua/v4/css/metro-all.min.css">
    <title>WFDesk - Login</title>
    <style>
        .login-form {
            width: 350px;
            height: auto;
            top: 50%;
            margin-top: -120px;
        }
    </style>
</head>
<body class="h-vh-100 bg-steel">
    <div class="login-form bg-white p-6 mx-auto border bd-default win-shadow">
        <span class="mif-lock mif-4x place-right" style="margin-top: -10px;"></span>
        <h2 class="text-light">Login to WFDesk</h2>
        <hr class="thin mt-4 mb-4 bg-white">
        <div class="form-group">
            <a style="text-decoration:none" href="<?php echo $url; ?>"><button class="command-button primary outline rounded">
                <span class="mif-google icon"></span>
                <span class="caption">
                    Continue with Google
                    <small>Use your @wordfence.com email</small>
                </span>
            </button></a>
        </div>
    </div>
</body>
</html>