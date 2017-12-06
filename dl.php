<?php
if (isset($_GET['chapter_id'])) {
    $id = intval($_GET['chapter_id']);
    
    if ($id !== 0) {
        include_once 'Admin/MangaCacheDataAdapter.php';
        include_once 'Admin/YandexDiskAdapter.php';
        include_once 'bd.php';
        
        $adapter = new YandexDiskAdapter($mysqli);
        $link = $adapter->getChapterLink($id);
        
        header('HTTP/1.1 302 Moved Permanently'); 
        header('Location: ' . $link); 
        exit(); 
    }
}

