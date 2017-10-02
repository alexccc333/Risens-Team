<?php
if (!isset($_GET['id']) || !isset($_GET['type'])) {
	// Если не задан ID серии или тип (сабы/озвучка) ничего не выводим
	return;
}

include 'bd.php'; // Подключаем базу данных
include 'Player/Player.php';
include 'Frame/Main.php';
include 'Frame/Extension.php';
include 'Log/Log.php';

$main = new Main(); // Создаем основу
$player = new Player(intval($_GET['id']), $_GET['type'], $mysqli); // Создаем плеер
$main->setPlayer($player); // Линкуем основу и плеер

include 'Player/head.php'; // Подключаем набор хедеров

$main->setHead($head); // Линкуем основу и хедеры
$main->printHead(); // Выводим блок <head></head>
$main->printBody(); // Выводим тело (плеер)