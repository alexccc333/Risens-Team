<?php
$id = intval($_GET['id']);
if ($id === 0) die();

include_once '../bd.php';
include_once '../Admin/EpisodeDataAdapter.php';

$adapter = new EpisodeDataAdapter($mysqli);
$eps = $adapter->getEpisodesByAnimeId($id, true);

echo json_encode($eps);
