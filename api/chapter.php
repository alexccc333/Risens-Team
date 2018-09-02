<?php
$id = intval($_GET['id']);
if ($id === 0) die();

include_once '../bd.php';
include_once '../Admin/YandexDiskAdapter.php';

$adapter = new YandexDiskAdapter($mysqli);
$chp = $adapter->getManga($id);
$needles = array('[', ']', '\'', ';');
$chp = str_replace($needles, '', $chp);
$chp = array_filter(explode(',', $chp));

echo json_encode($chp);
