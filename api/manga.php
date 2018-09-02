<?php
$id = intval($_GET['id']);
if ($id === 0) die();

include_once '../bd.php';
include_once '../Admin/ChapterDataAdapter.php';

$adapter = new ChapterDataAdapter($mysqli);
$chps = $adapter->getChaptersByMangaId($id);

echo json_encode($chps);
