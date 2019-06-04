<?php

require_once 'settings.php';

use google\appengine\api\users\UserService;

$user = UserService::getCurrentUser();

//not logged in, show login page
if (empty($user)) {
    include 'login.php';
    exit;
}

$email = strtolower($user->getEmail());

//email is not from wordfence, prompt user to logout
if (substr($email, -14) != '@wordfence.com') {
    include 'unauthorized.php';
    exit;
}
    
//user is accessing data.json file
if ($_SERVER['REQUEST_URI'] == '/data') {
    $data = file_exists($json_file) ? file_get_contents($json_file) : '';
    
    if (!empty($data)) {
        $json = json_decode($data, true);
        
        if (time() < $json['last_updated'] + 3600) { //still fresh data!
            echo $data;
            exit;
        }
    }
    
    //we should update data here
    include 'update.php';
    echo file_get_contents($json_file);
    
    exit;
}

include 'dashboard.php';
