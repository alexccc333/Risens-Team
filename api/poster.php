<?php
$id = intval($_GET['id']);
if ($id === 0) die();

include_once '../bd.php';
include_once '../Admin/AnimeDataAdapter.php';

$adapter = new AnimeDataAdapter($mysqli);
$res = $adapter->getAnimeById($id);

$poster = $res['poster'];

header( 'Location: ' . $poster, true, 303);
