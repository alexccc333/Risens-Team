<?php

class GetManga extends Main {
	protected $_id = 0;
	protected $_name = '';
	protected $_mysqli = null;
	protected $_data = null;
	protected $_currentChapterId = 0;
	protected $_allIds = array();
	protected $_allNames = array();
	protected $_currentChapter = 0;
	protected $_currentChapterAlias = '';

	const PAGES = 'pages';
	const DOWNLOAD_LINK = 'dl';
	const CHAPTER_NAME = 'name';
	
	const COL_ID = 'id';
	const COL_LINKS = 'links';
	const COL_DOWNLOAD = 'download';
	const COL_CHAPTER_NAME = 'chaptername';
	const COL_CHAPTER_ALIAS = 'name';
	const COL_NAME = 'name';
	
	public function __construct($id, $mysqli, $currentChapterId) {
		$this->_id = $id;
		$this->_mysqli = $mysqli;	
		
		$sql = 'SELECT id, name FROM chapters WHERE mangaid = ' . $id . ' ORDER BY (chapter+0) DESC';
		$result = $mysqli->query($sql);
		if ($result) {	
			$row = $result->fetch_assoc();
			$this->_currentChapterId = $row[self::COL_ID];
			$this->_currentChapterAlias = $row[self::COL_CHAPTER_ALIAS];
		}
		$result->free();
		
		if ($currentChapterId !== 0) {
			$this->_currentChapterId = $currentChapterId;
		}
	}
	
	public function getData() {
		$sql = 'SELECT * FROM chapters WHERE mangaid = ' . $this->_id . ' and id = ' . $this->_currentChapterId;
		$result = $this->_mysqli->query($sql);
		if ($result) {
			$linksFromDataBase = $result->fetch_assoc();
			$result->free();
			$this->_data[self::PAGES] = $linksFromDataBase[self::COL_LINKS];
			$this->_data[self::DOWNLOAD_LINK] = $linksFromDataBase[self::COL_DOWNLOAD];
			$this->_data[self::CHAPTER_NAME] = $linksFromDataBase[self::COL_CHAPTER_NAME];
		}
		
		$sql = 'SELECT id, name FROM chapters WHERE mangaid = ' . $this->_id . ' ORDER BY (chapter+0) ASC';
		$result = $this->_mysqli->query($sql);
		if ($result) {
			$i = 0;
			$currentChapter = 0;
			
			$row = $result->fetch_assoc();
			while ($row) {
				$this->_allIds[] = $row['id'];
				$this->_allNames[] = $row['name'];
				if ($row['id'] == $this->_currentChapterId) {
					$currentChapter = $i;
				}
				$i++;
				$row = $result->fetch_assoc();
			}
			
			$this->_allIds = array_reverse($this->_allIds);
			$this->_allNames = array_reverse($this->_allNames);
			
			if (!isset($_GET['chapter'])) {
				$currentChapter = count($this->_allNames) - 1;
			}
			
			$this->_currentChapter = $currentChapter;
			$result->free();
		}
		
		$sql = 'SELECT * from manga where id=' . $this->_id;
		$result = $this->_mysqli->query($sql);
		if ($result) {
			$row = $result->fetch_assoc();
			$this->_name = $row[self::CHAPTER_NAME];
		}
		
		return $this->_data;
	}
	
	public function printBody() {
		$logger = new Log($this->_mysqli);
		$logger->doLog($this);
		
		echo '<body style="background-color: #354050;">';
		
		$this->_printPagesAndDownloadLink();
		
		echo '<div class="container-fluid"><div class="row"><div class="text-center">';
		echo '<select data-live-search="true" data-size="10" class="selectpicker" id="pageselect" onChange="draw(this.selectedIndex)"></select>';
		echo '<script type="text/javascript">';
		echo file_get_contents('Scripts/MangaSelector.js');
		echo '</script>';
		
		echo '<a href="" target="_blank"><img src="http://risensteam.ru/images/lupa.png" width="32px" height="32px" style="cursor: pointer;"></a><br>';
		echo '<img id="before" style="display: none;">';
		echo '<a id="link"><img id="current" onclick="change_page_next();" style="cursor: pointer; margin: 0 auto;" class="img-responsive"></a>';
		echo '<img id="after" style="display: none;"><img id="original" style="display: none;"><br><br>';
		echo '<div class="btn-group"><a type="button" class="btn btn-primary btn-lg btn-success" onclick="change_page_prev();">Предыдущая страница</a>';
		echo '<a type="button" id="dlbtn" class="btn btn-primary btn-lg btn-success" href="" target="_blank">Скачать</a><a type="button" class="btn btn-primary btn-lg btn-success" onclick="change_page_next();">Следующая страница</a>';
		echo '</div><br>';
		
		echo '<script type="text/javascript">';
		echo file_get_contents('Scripts/MangaReader.js');
		echo '</script><br>';
			
		$tempIds = array_reverse($this->_allIds);
		$tempNames = array_reverse($this->_allNames);
		
		if (isset($tempIds[$this->_currentChapter - 1])) {
			echo '<a type="button" class="btn btn-primary btn-lg btn-success" href="?id=' . $this->_id . '&chapter=' . $tempIds[$this->_currentChapter - 1] . '">Предыдущая глава</a> ';
		}
		else {
			echo '<a type="button" class="btn btn-primary btn-lg btn-success disabled">Предыдущая глава</a> ';
		}
		echo '<select data-live-search="true" data-size="10" id="selchap" data-dropup-auto="false"  class="selectpicker dropup" onChange="location.href=\'?id=' . $this->_id;
		echo '&chapter=\' + this.options[this.selectedIndex].value;">';
		
		for ($i = 0; $i < count($tempNames); $i++) {
			if ($i === $this->_currentChapter) {
				echo '<option selected value="' . $tempIds[$i] . '">' . $tempNames[$i] . "</option>";
			}
			else {
				echo '<option value="' . $tempIds[$i] . '">' . $tempNames[$i] . "</option>";
			}
		}
		echo '</select>';
		
		if (isset($tempIds[$this->_currentChapter + 1])) {
			echo '<a type="button" class="btn btn-primary btn-lg btn-success" href="?id=' . $this->_id . '&chapter=' . $tempIds[$this->_currentChapter + 1] . '">Следующая глава</a>';
		}
		else {
			echo '<a type="button" class="btn btn-primary btn-lg btn-success disabled">Следующая глава</a> ';
		}
		
		echo '</div></div></div></body>';
	}
	
	public function getName() {
		return $this->_name;
	}
	
	public function getChapterAlias() {
		return $this->_currentChapterAlias;
	}
	
	protected function _printPagesAndDownloadLink() {
		echo '<h3 style="color: white">' . $this->_data[self::CHAPTER_NAME] . '</h3>';
		echo '<script>';
        $yandexAdapter = new YandexDiskAdapter($this->_mysqli);
        $data = $yandexAdapter->getManga('Manga/Golem Hearts/1', $this->_currentChapterId);
		echo 'var pages = ' . $data[0] . PHP_EOL;
		echo 'var dl = "' . $data[1] . '";';
		echo '</script>';
	}
}
