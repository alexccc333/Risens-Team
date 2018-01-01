<?php
if (isset($_GET['chapter_id'])) {
    $id = intval($_GET['chapter_id']);
    
    if ($id !== 0) {
		include_once 'Admin/DataAdapter.php';
        include_once 'Admin/YandexDiskAdapter.php';
        include_once 'bd.php';
        
        $adapter = new YandexDiskAdapter($mysqli);
        $link = $adapter->getChapterLink($id);
        
        header('HTTP/1.1 302 Moved Permanently'); 
        header('Location: ' . $link); 
        exit(); 
    }
}
elseif (isset($_GET['episode_id'])) {
	$id = intval($_GET['episode_id']);
	$type = isset($_GET['dub']) ? 'dub' : 'sub';
	
	if ($id !== 0) {
		include_once 'Admin/DataAdapter.php';
		include_once 'Player/Player.php';
        include_once 'bd.php';
		
		$player = new Player($id, $type , $mysqli);
		$player->loadVideo(true);
		$videoUrl = $player->getLoadedUrl();
		$subUrl = $player->getSubtitleLink();
		
		if ($videoUrl === '') {
			echo 'К сожалению, для этой серии скачка недоступна';
			return;
		}
		
		echo '<p>Скачайте ';
		echo '<a href="' . $videoUrl . '" download>видео</a> ';
		if ($subUrl !== '') {
			echo 'и <a href="' . $subUrl . '" download>субтитры</a> к нему';
			echo '<hr>Кликните правой кнопкой по ссылке и нажмите "Сохранить как", чтобы точно скачать файл, также не забудьте установить <a href="http://risensteam.ru/fonts.zip" download>шрифты</a> для корректного просмотра!</p>';
		}
	}
}
