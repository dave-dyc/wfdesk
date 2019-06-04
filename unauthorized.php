<?php

use google\appengine\api\users\UserService;

$url = UserService::createLogoutUrl('/');

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdn.metroui.org.ua/v4/css/metro-all.min.css">
    <title>WFDesk - Unauthorized</title>
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
        <h2 class="text-light">Sorry...</h2>
        <hr class="thin mt-4 mb-4 bg-white">
        You must use your @wordfence.com email to access WFDesk.
        <div class="form-group">
            <a style="text-decoration:none" href="<?php echo $url; ?>"><button style="margin-top:25px" class="button alert outline rounded">
                <span class="mif-exit icon"></span>
                <span class="caption">Logout</span>
            </button></a>
        </div>
    </div>
</body>
</html>