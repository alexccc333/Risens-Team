<?php

class Log {
	protected $_mysqli = null;
	protected $_table = 'logs';
	
	public function __construct($mysqli) {
		$this->_mysqli = $mysqli;
	}
	
	public function doLog($instance) {
		$ip = $_SERVER['REMOTE_ADDR'];
		if ($instance instanceof Player) {
			$name = $instance->getName();
			$episodeName = $instance->getEpisodeName();
			$type = $instance->getType();
			$logString = 'Anime = ' . $name . ' | Episode = ' . $episodeName . ' | Type = ' . $type . ' FROM ' . $ip;
			
			$this->_insert($logString);
		}
		
		if ($instance instanceof GetManga) {
			$name = $instance->getName();
			$chapterName = $instance->getChapterAlias();
			$logString = 'Manga = ' . $name . ' | Chapter = ' . $chapterName . ' FROM ' . $ip;
			
			$this->_insert($logString);
		}
	}
	
	protected function _insert($string) {
		$sql = 'INSERT INTO `' . $this->_table . '` (`date`, `value`) VALUES (NULL, \'' . $string . '\');';
		
		$this->_mysqli->query($sql);
	}
}
