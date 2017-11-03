<?php

include 'bd.php';
include 'Admin/Router.php';
include 'Admin/User.php';
include 'Frame/Extension.php';
include 'Admin/header.php';

$currentUser = new User();
$form = new Router();

$form->setHead($head);
$form->printHead();

if (isset($_POST['login']) && isset($_POST['password'])) {
    $login = strtolower($_POST['login']);
    $pass = hash('sha512', $_POST['password'] . SALT);
    $status = $currentUser->login($login, $pass, $mysqli);
}
elseif (isset($_GET['logout'])) {
    unset($_COOKIE['user_cookie']);
    setcookie('user_cookie', '', time() - 3600);
}
elseif (isset($_COOKIE['user_cookie'])) {
    $currentUser->loginByCookie($_COOKIE['user_cookie'], $mysqli);
}

$form->setUser($currentUser);
$form->printBody();
