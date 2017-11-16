<?php

if (!isset($_GET['id'])) {
	// Если не задан ID манги ничего не выводим
	return;
}

include 'bd.php';
include 'Frame/Main.php';
include 'Frame/Extension.php';
include 'Log/Log.php';
include 'Frame/GetManga.php';
include 'Admin/YandexDiskAdapter.php';

$id = intval($_GET['id']);
$currentChapter = isset($_GET['chapter']) ? intval($_GET['chapter']) : 0;
$frame = new GetManga($id, $mysqli, $currentChapter);

if (!$frame->getData()) {
	return;
}

include 'Frame/mangaHead.php'; // Подключаем набор хедеров
$frame->setHead($head); // Линкуем основу и хедеры
$frame->printHead();
$frame->printBody();