<?php

class ChapterDataAdapter extends DataAdapter {
    const COL_ID = 'id';
    const COL_MANGA_ID = 'mangaid';
    const COL_NUMBER = 'chapter';
    const COL_NAME = 'name';
    const COL_CHAPTER_NAME = 'chaptername';
    const COL_LINKS = 'links';
    const COL_DOWNLOAD = 'download';
    
    public function getChaptersByMangaId($mangaId) {
        $sql = $this->_mysqli->prepare('SELECT id, name from chapters WHERE mangaid=?');
        $sql->bind_param('i', $mangaId);
        $status = $sql->execute();
        
        if ($status) {
            $result = $sql->get_result();
            $returnArray = array();
            
			$row = $result->fetch_assoc();
            while ($row) {
                $returnArray[] = $row;
                $row = $result->fetch_assoc();
            }
			$result->free();
            
			return $returnArray;
        }
    }
    
    public function getChapterById($id) {
        $sql = $this->_mysqli->prepare('SELECT * from chapters WHERE id=?');
        $sql->bind_param('i', $id);
        $status = $sql->execute();
		
		if ($status) {
            $result = $sql->get_result();
			$row = $result->fetch_assoc();
			$result->free();
			
			return $row;
		}
        
		return false;
    }
    
    public function updateChapter($id, $name, $number, $chapterName, $links, $download) {
        $sql = $this->_mysqli->prepare('UPDATE `chapters` SET `name`=?,`chapter`=?,`chaptername`=?,`links`=?,`download`=? WHERE `id`=?');
        $sql->bind_param('sssssi', $name, $number, $chapterName, $links, $download, $id);
        $status = $sql->execute();
        
        return $status;
    }
    
    public function removeChapter($id) {
        $sql = $this->_mysqli->prepare('DELETE FROM `chapters` WHERE `id` = ?');
        $sql->bind_param('i', $id);
        $status = $sql->execute();
        
        return $status;
    }
    
    public function createNewChapter($name, $number, $mangaId, $chapterName, $links, $download) {
        $sql = $this->_mysqli->prepare('INSERT INTO `chapters`(`id`, `name`, `chapter`, `mangaid`, `chaptername`, `links`, `download`) VALUES(NULL,?,?,?,?,?,?)');
        $sql->bind_param('ssisis', $name, $number, $mangaId, $chapterName, $links, $download);
        $status = $sql->execute();
        
        if ($status) {
            return $sql->insert_id;
        }
        else {
            return false;
        }
    }
}
