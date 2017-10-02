<?php

class GetAnime extends Main {
	protected $_id = 0;
	protected $_poster = '';
	protected $_name = '';
	protected $_mysqli = null;
	protected $_data = null;

	const SUB_IDS = 'subids';
	const DUB_IDS = 'dubids';
	const SUB_PATHS = 'subpaths';
	const DUB_PATHS = 'dubpaths';
	const NAMES = 'names';
	const IDS = 'ids';
	
	const COL_SUB_VIDEO_ID = 'subvideoid';
	const COL_DUB_VIDEO_ID = 'dubvideoid';
	const COL_SUB_PATH = 'subpath';
	const COL_DUB_PATH = 'dubsubpath';
	const COL_NAME = 'name';
	const COL_ID = 'id';
	
	public function __construct($id, $mysqli) {
		$this->_id = $id;
		$this->_mysqli = $mysqli;
		$this->_loadPoster();		
	}

	protected function _loadPoster() {
		$sql = 'SELECT poster, name FROM anime WHERE id = ' . $this->_id;
		$result = $this->_mysqli->query($sql);
		$fr = $result->fetch_assoc();
		$this->_poster = $fr['poster'];
		$this->_name = $fr['name'];
	}

	public function getPoster() {
		return $this->_poster;
	}
	
	public function getData() {
		if (!$this->_data) {
			$sql     = 'SELECT * FROM episodes WHERE anime_id = ' . $this->_id . 
						' ORDER BY (number+0) DESC';
			$result = $this->_mysqli->query($sql);

			if ($result) {
				$returnArray = array(
					self::SUB_IDS	=> '',
					self::DUB_IDS	=> '',
					self::SUB_PATHS	=> '',
					self::DUB_PATHS	=> '',
					self::NAMES		=> '',
					self::IDS		=> ''
				);

				$row = $result->fetch_assoc();
				while($row) {
					$returnArray[self::SUB_IDS] .= '\'' . $row[self::COL_SUB_VIDEO_ID] . '\', ';
					$returnArray[self::DUB_IDS] .= '\'' . $row[self::COL_DUB_VIDEO_ID] . '\', ';
					$returnArray[self::SUB_PATHS] .= '\'' . $row[self::COL_SUB_PATH] . '\', ';
					$returnArray[self::DUB_PATHS] .= '\'' . $row[self::COL_DUB_PATH] . '\', ';
					$returnArray[self::NAMES] .= '\'' . $row[self::COL_NAME] . '\', ';
					$returnArray[self::IDS] .= '\'' . $row[self::COL_ID] . '\', ';

					$row = $result->fetch_assoc();
				}

				$this->_data = $returnArray;
				return $returnArray;
			}
			
			$this->_data = false;
			return false;
		}
		else {
			return $this->_data;
		}
	}
	
	public function printBody() {
		echo '<body style="background-color: black; margin: 0px;">';
		echo '<div id="selectors">
			<select id="episodes" class="selectpicker" onchange="changeEpisode(this.selectedIndex)"></select>
			<select id="subdub" class="selectpicker" onchange="changeDubSub(this.options[this.selectedIndex].innerHTML)"></select>
			</div>';
		echo '<video id="mv" class="video-js vjs-default-skin vjs-big-play-centered">
			<source id ="vide" src="player/newzastavka.mp4" type="video/mp4">
			</video>';
		echo '<iframe id="player" src="" width="100%" height="100%" frameborder="0" allowfullscreen=""></iframe>';
		
		echo '<script>';
		echo 'var poster = \'' . $this->_poster . '\';';
		echo 'var eps = [ ' . $this->_data[self::NAMES] . ' ];';
		echo 'var ids = [ ' . $this->_data[self::IDS] . ' ];';
		echo 'var viS = [ ' . $this->_data[self::SUB_IDS] . ' ];';
		echo 'var suS = [ ' . $this->_data[self::SUB_PATHS] . ' ];';
		echo 'var viD = [ ' . $this->_data[self::DUB_IDS] . ' ];';
		echo 'var suD = [ ' . $this->_data[self::DUB_PATHS] . ' ];';
		
		echo file_get_contents('Scripts/GetAnimeSetup.js');
		echo '</script></body>';
	}
	
	public function getName() {
		return $this->_name;
	}
}