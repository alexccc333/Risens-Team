<?php

include 'bd.php';
include 'Admin/LoginForm.php';
include 'Admin/User.php';

$currentUser = new User();
$form = new LoginForm();
if (isset($_POST['login']) && isset($_POST['password'])) {
    $login = strtolower($_POST['login']);
    $pass = hash('sha512', $_POST['password'] . $salt);
    $currentUser->login($login, $pass, $mysqli);
}
elseif (isset($_GET['logout'])) {
    unset($_COOKIE['user_cookie']);
    setcookie('user_cookie', '', time() - 3600);
}
elseif (isset($_COOKIE['user_cookie'])) {
    $currentUser->loginByCookie($_COOKIE['user_cookie'], $mysqli);
}

if ($currentUser->isAnon()){ 
    $form->printForm();
    return 0;
}

$form->printLogOut();