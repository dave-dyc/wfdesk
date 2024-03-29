<?php

require_once 'vendor/autoload.php';
include 'settings.php';

session_start();

if (empty($_SESSION['email']) && isset($_COOKIE['token'])) { 
    $client = new Google_Client(['client_id' => $client_id]);
    $payload = $client->verifyIdToken($_COOKIE['token']);
    
    if (isset($payload['email']))
        $_SESSION['email'] = strtolower($payload['email']);
}

if (empty($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

$email = $_SESSION['email'];
$users = json_decode(file_get_contents($users_file), true);

if (empty($users[$email])) {
    include 'unauthorized.php';
    exit;
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <link rel="stylesheet" href="/static/metro-all.min.css">
    <title>WFDesk - Dashboard</title>

    <style>
        .pagination {
            flex-wrap: wrap;
        }
        
        .d-menu {
            background-color: #e4e4e4;
        }
    </style>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="/static/metro.min.js"></script>
</head>
<body>
<div class="container">
    <h4 class="text-center" style="margin-bottom:25px;margin-top:25px">
        WFDesk 
        <div class="dropdown-button">
            <button class="button dropdown-toggle account_display">Default Account</button>
            <ul class="d-menu" data-role="dropdown">
                <li class="switch_user" data-user="Default Account"><a>Default Account</a></li>
                <?php
                    foreach ($users as $user => $data)
                        echo '<li class="switch_user" data-user="' . htmlentities($data[0]) . '"><a>' . htmlentities($data[0]) . ' - ' . htmlentities($user) . '</a></li>';
                        
                    if ($users[$_SESSION['email']][1] == 'Admin')
                        echo '<li class="bg-blue fg-white"><a target="_blank" href="users.php">Manage users</a></li>';
                ?>
                <li class="bg-red fg-white"><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </h4>
    
    <div class="text-center">
        <button class="command-button primary outline rounded filter_assigned" style="margin-right:25px">
            <span class="mif-pencil icon"></span>
            <span class="caption">
                Assigned Tickets
                <small>Waiting for your response</small>
            </span>
        </button>

        <button class="command-button primary outline rounded filter_pending" style="margin-right:25px">
            <span class="mif-spinner icon"></span>
            <span class="caption">
                Pending Tickets
                <small>Waiting for the customer</small>
            </span>
        </button>
                
        <button class="command-button primary outline rounded filter_unassigned">
            <span class="mif-assignment icon"></span>
            <span class="caption">
                Unassigned Tickets
                <small>Threads awaiting support</small>
            </span>
        </button>
    </div>

    <div class="d-flex flex-justify-center">
        <div id="activity" style="margin-top:25px" data-role="activity" data-type="cycle" data-style="color" style="display: none"></div>
    </div>

    <table id="t1" class="table striped table-border mt-4 row-hover"
           data-role="table"
           data-cls-component="mt-10"
           data-rows="10"
           data-pagination="true"
           data-show-rows-steps="false"
           data-show-search="false"
           data-show-all-pages="false"
           data-on-data-load="$('#activity').show()"
           data-on-data-loaded="$('#activity').hide()"
    ></table>
</div>

<script>
$(document).ready(function() {
    function switch_user(name) {
        $('.account_display').html(name);
        
        if (name == 'Default Account') {
            $('.filter_assigned,.filter_pending').hide();
            $('.filter_unassigned').trigger('click');
        } else {
            $('.filter_assigned,.filter_pending').show();
            $('.filter_assigned').trigger('click');
        }
    }
    
    $('.switch_user').click(function() {
        var user = $(this).data('user');
        switch_user(user);
    });

    $('#t1').data('table').loadData('data.php');
    
    function resetOutline() {
        $('.filter_assigned,.filter_pending,.filter_unassigned').addClass('outline');
    }

    $('.filter_assigned').click(function() {
        resetOutline();
        $(this).removeClass('outline');
    
        var current_user = $('.account_display').text();

        $('#t1').data('table').removeFilters();
        $('#t1').data('table').addFilter(function(row, heads) {
            return row[6] == current_user && row[7] == 'assigned';
        }, true);
    });

    $('.filter_pending').click(function() {
        resetOutline();
        $(this).removeClass('outline');
    
        var current_user = $('.account_display').text();

        $('#t1').data('table').removeFilters();
        $('#t1').data('table').addFilter(function(row, heads) {
            return row[6] == current_user && row[7] == 'pending';
        }, true);
    });

    $('.filter_unassigned').click(function() {
        resetOutline();
        $(this).removeClass('outline');
        
        $('#t1').data('table').removeFilters();
        $('#t1').data('table').addFilter(function(row, heads) {
            return row[6] == '';
        }, true);
    });
    
    <?php
        //currently logged in user has a worker account
        if (isset($users[$email]))
            echo "switch_user(" . json_encode($users[$email][0]) . ");\n";
        else {
            echo "$('.filter_assigned,.filter_pending').hide();\n";
            echo "$('.filter_unassigned').trigger('click');";
        }
    ?>
});
 
</script>

</body>
</html>