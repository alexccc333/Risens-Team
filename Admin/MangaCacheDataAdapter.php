<?php
include 'Admin/DataAdapter.php';

class MangaCacheDataAdapter extends DataAdapter {
    
    const COL_ID = 'id';
    const COL_CHAPTER_ID = 'chapter_id';
    const COL_EXPIRES = 'expires';
    const COL_PAGES = 'pages';
    const COL_LINK = 'link';
    const CACHE_LIFETIME = 7200; // 2 часа
    
    public function getMangaFromCache($id) {
        $sql = $this->_mysqli->prepare('SELECT pages, link, expires, id from manga_cache WHERE chapter_id=?');
        $sql->bind_param('i', $id);
        $status = $sql->execute();
		
		if ($status) {
            $result = $sql->get_result();
			$row = $result->fetch_assoc();
			$result->free();
			
            if (intval($row[self::COL_EXPIRES]) <= time()) {
                $this->_removeMangaFromCache($row[self::COL_ID]);
                return false;
            }
            
			return $row;
		}
        
		return false;
    }
    
    public function addMangaCache($chapterId, $pages, $link) {
        $time = time() + self::CACHE_LIFETIME;
        
        $sql = $this->_mysqli->prepare('INSERT INTO `manga_cache`(`id`, `chapter_id`, `expires`, `pages`, `link`) VALUES(NULL,?,?,?,?)');
        $sql->bind_param('isss', $chapterId, $time, $pages, $link);
        $status = $sql->execute();
        
        if ($status) {
            return $sql->insert_id;
        }
        else {
            return false;
        }
    }
    
    protected function _removeMangaFromCache($id) {
        $sql = $this->_mysqli->prepare('DELETE FROM `manga_cache` WHERE `id` = ?');
        $sql->bind_param('i', $id);
        $status = $sql->execute();
        
        return $status;
    }
}
