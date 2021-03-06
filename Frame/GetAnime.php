<?php

class GetAnime extends Main {
	protected $_id = 0;
	protected $_poster = '';
	protected $_name = '';
    protected $_redirect = '';
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

	public function __construct($id, $mysqli, $type='risens') {
		$this->_id = $id;
		$this->_mysqli = $mysqli;
		$this->_loadPosterAndRedirect();
	}

	protected function _loadPosterAndRedirect() {
		$sql = 'SELECT poster, name, redirect FROM anime WHERE id = ' . $this->_id;
		$result = $this->_mysqli->query($sql);
		$fr = $result->fetch_assoc();
		$this->_poster = $fr['poster'];
		$this->_name = $fr['name'];
		$this->_redirect= $fr['redirect'];
	}

	public function getPoster() {
		return $this->_poster;
	}

	public function getData() {
		if (!$this->_data) {
			$sql = 'SELECT * FROM episodes WHERE anime_id = ' . $this->_id .
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

	public function printBody($marker = 'risens') {
        if ($this->_redirect !== '') {
            if ($this->_data[self::SUB_IDS] === '' && $this->_data[self::DUB_IDS] === '') {
                $showPlayer = false;
            } else {
                /*include('SxGeo.php');
                $sxGeo = new SxGeo();
                $ip = $_SERVER['REMOTE_ADDR'];
                $country = $sxGeo->getCountry($ip);*/
                $country = $_SERVER["HTTP_CF_IPCOUNTRY"];
                $showPlayer = $country !== 'RU' && $country !== 'FR';
            }
        }
        else {
            $showPlayer = true;
        }

				switch ($marker) {
					case 'waka':
						$preroll = 'player/wakanim_temp_2.mp4';
						break;
                    case 'sovetromantica':
                        $preroll = 'player/sovetromantica.mp4';
                        break;
					case 'risens':
					default:
						$preroll = 'player/newzastavka.mp4';
						break;
				}

        if ($showPlayer) {
            echo '<body style="background-color: black; margin: 0px;">';
            echo '<div id="selectors">
                <select id="episodes" class="selectpicker" onchange="changeEpisode(this.selectedIndex)"></select>
                <select id="subdub" class="selectpicker" onchange="changeDubSub(this.options[this.selectedIndex].innerHTML)"></select>
				<button class="btn btn-secondary hidden-md-down" type="button" id="dropdownDownloadButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					Скачать
				</button>
				<div class="dropdown-menu dropdown-content" aria-labelledby="dropdownDownloadButton">
					<iframe id="downloadContent" width="100%"></iframe>
				</div>

                </div>';
            echo '<video id="mv" class="video-js vjs-default-skin vjs-big-play-centered" playsinline>
                <source id ="vide" src="'. $preroll . '" type="video/mp4">
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
        else {
            echo '<body style="background-color: black; margin: 0px;">';
            echo '<div class="overlay"><a href="' . $this->_redirect . '" target="_blank">';
            echo '<img class="thumbnail" width=100% height=100% src="' . $this->_poster . '"></a>';
			echo '<a href="' . $this->_redirect . '" class="playWrapper" target="_blank">';
			echo '<span class="playBtn"><img src="https://risens.team/risensteam/Player/play-button.png" width="50" height="50" alt=""></span>';
			echo '</a></div></body>';
        }
	}

	public function getName() {
		return $this->_name;
	}
}
