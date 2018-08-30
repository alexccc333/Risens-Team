<?php
if (!isset($_GET['id'])) {
	// Если не задан ID аниме ничего не выводим
	return;
}

include 'bd.php';
include 'Frame/Main.php';
include 'Frame/Extension.php';
include 'Frame/GetAnime.php';

$id = intval($_GET['id']);
switch($_GET['marker']) {
	case 'waka':
		$marker = 'waka';
		break;
	case 'sovetromantica':
	    $marker = 'sovetromantica';
	    break;
	default:
		$marker = 'risens';
		break;
}
$frame = new GetAnime($id, $mysqli);

if (!$frame->getData()) {
	return;
}

include 'Frame/head.php'; // Подключаем набор хедеров
$frame->setHead($head); // Линкуем основу и хедеры
$frame->printHead();
$frame->printBody($marker);
