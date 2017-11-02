<?php 
$mysqli = new mysqli("localhost", "root", "", "risensteam");
if ($mysqli->connect_errno) {
    echo "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
else {
    $mysqli->set_charset("utf8");
    $salt = 'u2yDJR5aHTmrctQLwWFQUVCX7jWt7v4s9YEXkwkQ';
}
?>