<?php

include 'settings.php';

session_start();

$roles = ['Worker', 'Admin'];
$users = [
    'wftest@wordfence.com' => ['wftest', 'Admin']
];
if (file_exists($users_file))
    $users = json_decode(file_get_contents($users_file), true);

function redir() {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['email']))
    redir();

$email = $_SESSION['email'];
if (empty($users[$email]))
    redir();

$data = $users[$email];
if ($data[1] != 'Admin')
    redir();

function save_users($users_file, $users) {
    file_put_contents($users_file, json_encode($users));

}

$handlers = [
    'change_role' => function() use ($users_file, &$users, $roles) {
        if (empty($_POST['email']) || empty($_POST['role']))
            return;
        
        $email = substr($_POST['email'], 0, 128);
        $role = $_POST['role'];
        
        if (!in_array($role, $roles) || !isset($users[$email]) || $_SESSION['email'] == $email)
            return;
        
        $users[$email][1] = $role;
        save_users($users_file, $users);
    },
    
    'remove_user' => function() use ($users_file, &$users) {
        if (empty($_POST['email']))
            return;
        
        $email = substr($_POST['email'], 0, 128);
        
        if (!isset($users[$email]) || $_SESSION['email'] == $email)
            return;
        
        unset($users[$email]);
        save_users($users_file, $users);
    },
    
    'create_user' => function() use ($users_file, &$users) {
        if (empty($_POST['email']) || empty($_POST['alias']) || empty($_POST['role']))
            return;
        
        $email = substr($_POST['email'], 0, 128);
        $alias = substr($_POST['alias'], 0, 128);
        $role = $_POST['role'];
        
        if (isset($users[$email]))
            return;
        
        $users[$email] = [$alias, $role];
        save_users($users_file, $users);
    }
];

if (isset($handlers[$_POST['action']]))
    $handlers[$_POST['action']]();

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <link rel="stylesheet" href="https://cdn.metroui.org.ua/v4/css/metro-all.min.css">
    <title>WFDesk - Users</title>

    <style>
        .pagination {
            flex-wrap: wrap;
        }
        
        .d-menu {
            background-color: #e4e4e4;
        }
    </style>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdn.metroui.org.ua/v4/js/metro.min.js"></script>
</head>
<body>
<div class="container">

    <table class="table">
    <thead>
        <tr>
        <th>Email Address</th>
        <th>Forum Username</th>
        <th>Role</th>
        <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($users as $email => $data) {
            echo '<tr data-email="' . htmlentities($email) . '">';
            echo '<td>' . htmlentities($email) . '</td>';
            echo '<td>' . htmlentities($data[0]) . '</td>';
            $disabled = $_SESSION['email'] == $email ? ' disabled' : '';
            
            echo '<td><select class="change_role"' . $disabled . '>';
            foreach ($roles as $role) {
                $selected = $data[1] == $role ? ' selected' : '';
                echo '<option value="' . $role . '"' . $selected . '>' . $role . '</option>';
            }
            echo '</select></td><td>';
            
            if ($_SESSION['email'] != $email)
                echo '<button class="button alert remove_user">Remove</button>';
            
            echo '</td></tr>';
        }
    ?>
    <tr>
        <form method="post" action="">
            <input type="hidden" name="action" value="create_user">
            <td><input name="email" type="email" required maxlength="128"></td>
            <td><input name="alias" type="text" required maxlength="128"></td>
            <td><select name="role"><?php
            
            foreach (array_reverse($roles) as $role)
                echo '<option value="' . $role . '"' . $selected . '>' . $role . '</option>';
            
            ?></select></td>
            <td><button class="button primary" type="submit">Create</button></td>
        </form>
    </tr>
    
    </tbody>
    </table>
</div>

<script>

function post(fields) {
    var form = document.createElement("form");
    document.body.appendChild(form);
    form.method = "post";
    form.action = "";
    
    var len = fields.length;
    for (var i = 0; i < len; i++) {
        var field = fields[i];
        var f = document.createElement("input");
        f.name = field[0];
        f.type = 'hidden'
        f.value = field[1];
        form.appendChild(f);
    }
    
    form.submit();
}

$('.change_role').change(function() {
    var role = this.value;
    var email = $(this).parent().parent().data('email');
    
    post([
        ['action', 'change_role'],
        ['email' , email],
        ['role'  , role]
    ]);
});

$('.remove_user').click(function() {
    if (!confirm('Are you sure you want to remove this user?'))
        return;
    
    var email = $(this).parent().parent().data('email');
    
    post([
        ['action', 'remove_user'],
        ['email' , email]
    ]);
});

</script>

</body>
</html>