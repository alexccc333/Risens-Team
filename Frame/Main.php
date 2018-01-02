<?php

class Main {
	// Основной класс для формирования страницы
	/**
	 * хедеры
	 * @var Extension[]
	 */
	protected $_head = array();
	/**
	 * плеер
	 * @var Player
	 */
	protected $_player = null;
	
	public function setPlayer(Player $player) {
		$this->_player = $player;
	}
	
	public function setHead($head) {
		$this->_head = $head;
	}
	
	public function printHead() {
		echo '<head>';
		
		foreach ($this->_head as $header) {
			echo $header;
		}
		
		echo '</head>';
	}
	
	public function printBody() {
		$this->_player->loadVideo();
		
		echo '<body style=" margin: 0px;"><div class="player-wrapper">';
		echo '<video id="player" class="video-js vjs-default-skin vjs-big-play-centered" playsinline>';
		echo '<source id ="vide" src="' . $this->_player->getLoadedUrl() . '" type="video/mp4"></video></div>';
		
		echo '<script>';
		echo 'subLink = "' . $this->_player->getSubtitleLink() . '";';
		echo file_get_contents('Scripts/PlayerSetup.js');
		echo '</script></body>';
	}
}