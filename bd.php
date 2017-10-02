<?php 
$mysqli = new mysqli("localhost", "root", "", "risensteam");
if ($mysqli->connect_errno) {
    echo "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
else {
	$mysqli->set_charset("utf8");
}
?>