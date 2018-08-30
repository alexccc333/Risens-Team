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
		echo '<script src="//player.h-cdn.com/loader.js?customer=risens" crossorigin="anonymous" async></script>';
		echo '<script src="//player2.h-cdn.com/hola_player.js?customer=risens"></script>';
		foreach ($this->_head as $header) {
			echo $header;
		}

		echo '</head>';
	}

	public function printBody() {
		$this->_player->loadVideo();

		if ($this->_player->isWaka()) {
			echo '<body style="margin: 0px;">';
			echo '<iframe style="width:	 100%; height: 464px" src="' . $this->_player->getLoadedUrl() . '" scrolling="no" frameborder="0" allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true"  allow="encrypted-media"></iframe>';
			echo '</body>';
		}
			else {
			echo '<body style=" margin: 0px;"><div class="player-wrapper">';
			echo '<video id="player" class="video-js vjs-default-skin vjs-big-play-centered" playsinline>';
			echo '<source id ="vide" src="' . $this->_player->getLoadedUrl() . '" type="video/mp4"></video>';

			echo '<script>';
			echo 'subLink = "' . $this->_player->getSubtitleLink() . '";';
			echo file_get_contents('Scripts/PlayerSetup.js');
			echo '</script></body>';
		}
	}
}
