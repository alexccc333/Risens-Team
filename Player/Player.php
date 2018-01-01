<?php 
class Player {
		
	protected $_id = 0;
	protected $_animeId = 0;
	protected $_mysqli = null;
	protected $_url = '';
	protected $_type = '';
	protected $_loadedUrl = '';
	protected $_subtitleLink = '';
	protected $_name = '';
	protected $_episodeName = '';

	const DUB_PATH_COL = 'dubsubpath';
	const SUB_PATH_COL = 'subpath';
	const PATH_MATCH = array (
		'sub' => self::SUB_PATH_COL,
		'dub' => self::DUB_PATH_COL,
	);
	
	const SIBNET_URL = 'http://video.sibnet.ru';
	const SIBNET_VIDEO_URL = 'http://video.sibnet.ru/video';
	const SIBNET_SHELL_URL = 'http://video.sibnet.ru/shell.php?videoid=';
	
	public function __construct($id, $type, $mysqli) {
		$this->_id = $id;
		$this->_type = $type;
		$this->_mysqli = $mysqli;
	}
	
	protected function _getM3u8Content() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->_url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$a = curl_exec($ch); // $a will contain all headers

		$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // This is what you need, it will return you the last effective URL
		curl_close($ch);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		$raw = curl_exec($ch);
		curl_close($ch);
		return $raw;
	}
	
	protected function _setAnimeInfo() {
		$sql = "SELECT * FROM anime WHERE id=".$this->_animeId;
		$result = $this->_mysqli->query($sql);
		$fr = $result->fetch_assoc();
		$this->_name = $fr['name'];
	}
	
	protected function _checkForScam() {
		return !is_numeric($this->_id) || 
			($this->_type !== 'sub' && $this->_type !== 'dub');
	}
	
	public function loadVideo($noLog = false) {
		if ($this->_checkForScam()) {
			return '';
		}
		$sql = 'SELECT * FROM episodes WHERE id = ' . $this->_id;
		$result = $this->_mysqli->query($sql);
		if ($result) {
			$playerRow = $result->fetch_assoc();
			
			if (($playerRow[$this->_type . 'videoid']) === '') {
				return;
			}
			
			$this->_episodeName = $playerRow['name'];
			$this->_animeId = $playerRow['anime_id'];
			$this->_setAnimeInfo();
			$videoId = $playerRow[$this->_type . 'videoid'];
			$this->_subtitleLink = $playerRow[self::PATH_MATCH[$this->_type]];
			$url = self::SIBNET_VIDEO_URL . $videoId;
			$videoPage = file_get_contents($url);
			$pos1 = strpos($videoPage, '/v/');
			$pos2 = strpos($videoPage, '/v/', $pos1 + 3);
			$pos3 = strpos($videoPage, '.m3u8', $pos2);
			$newLink = self::SIBNET_URL . substr($videoPage, $pos2, $pos3 - $pos2) . '.m3u8';
			$this->_url = $newLink;

			$data = $this->_getM3u8Content();
			$pos1 = strpos($data, 'http');
			$pos2 = strpos($data, '&noip=1');

			$mp4Link = substr($data, $pos1, ($pos2 - 4) - $pos1) . '&noip=1';
			$this->_loadedUrl = str_replace('.ts', '.mp4', $mp4Link);
		}
		else {
			throw new Exception("Episode not found");
		}
		
		if (!$noLog) {
			$logger = new Log($this->_mysqli);
			$logger->doLog($this);
		}
	}
	
	public function getLoadedUrl() {
		return $this->_loadedUrl;
	}
	
	public function getSubtitleLink() {
		return $this->_subtitleLink;
	}
	
	public function getName() {
		return $this->_name;
	}
	
	public function getType() {
		return $this->_type === 'sub' ? 'SUB' : 'DUB';
	}
	
	public function getEpisodeName() {
		return $this->_episodeName;
	}
}