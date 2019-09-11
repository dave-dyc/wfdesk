<?php

include 'settings.php';

session_start();

if (isset($_SESSION['email'])) {
    header('Location: index.php');
    exit;
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <script src="https://apis.google.com/js/platform.js"></script>
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
            <button id="google_auth" class="command-button primary outline rounded">
                <span class="mif-google icon"></span>
                <span class="caption">
                    Continue with Google
                    <small>Use your @wordfence.com email</small>
                </span>
            </button>
        </div>
    </div>
    <script>
    gapi.load('auth2', function() {
        auth2 = gapi.auth2.init({
            client_id: '<?php echo $client_id; ?>',
            cookiepolicy: 'single_host_origin',
            prompt: 'select_account'
        });
        
        auth2.attachClickHandler(document.getElementById('google_auth'), {}, function(user) {
            var token = user.getAuthResponse().id_token;
            
            document.cookie = 'token=' + token;
            location.replace('index.php');
        }, function(error) {});
    });
    </script>
    
</body>
</html>