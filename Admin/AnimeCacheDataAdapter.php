<?php
require_once 'DataAdapter.php';

class AnimeCacheDataAdapter extends DataAdapter {

    const COL_ID = 'id';
    const COL_EXPIRES = 'expires';
    const COL_LINK = 'link';

    const TABLE_NAME = 'anime_cache';

    private $_lastCache;

    public function addAnimeCache($videoId, $expires, $link) {
      $sql = $this->_mysqli->prepare('INSERT INTO `' . self::TABLE_NAME . '` (`' . self::COL_ID . '`, `' . self::COL_EXPIRES . '`, `' . self::COL_LINK . '`) VALUES(?,?,?)');
      $sql->bind_param('iss', $videoId, $expires, $link);
      $status = $sql->execute();

      if ($status) {
          return $sql->insert_id;
      }
      else {
          return false;
      }
    }

    public function getAnimeFromCache($videoId) {
      $sql = $this->_mysqli->prepare('SELECT `' . self::COL_EXPIRES . '`, `' . self::COL_LINK . '` from `' . self::TABLE_NAME . '` WHERE `' . self::COL_ID . '` =?');
      $sql->bind_param('i', $videoId);
      $status = $sql->execute();

      if ($status) {
        $result = $sql->get_result();
  			$row = $result->fetch_assoc();
  			$result->free();

        if (intval($row[self::COL_EXPIRES]) <= time()) {
          $this->_removeAnimeFromCache($videoId);
          return false;
        }

        $this->_lastCache = $row;
  			return $row[self::COL_LINK];
  		}

  		return false;
    }

    protected function _removeAnimeFromCache($videoId) {
        $sql = $this->_mysqli->prepare('DELETE FROM `' . self::TABLE_NAME . '` WHERE `' . self::COL_ID . '` = ?');
        $sql->bind_param('i', $videoId);
        $status = $sql->execute();

        return $status;
    }

    public function clearCache() {
        $sql = $this->_mysqli->prepare('TRUNCATE TABLE `' . self::TABLE_NAME . '`');
        $status = $sql->execute();

        return $status;
    }

    public function getLastCacheExpire() {
      return $this->_lastCache[self::COL_EXPIRES];
    }

    /*
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

			return $row[MangaCacheDataAdapter::COL_PAGES];
		}

		return false;
    }

    public function addMangaCache($chapterId, $pages, $link = '') {
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

    public function removeMangaFromCacheByChapterId($id) {
        $sql = $this->_mysqli->prepare('DELETE FROM `manga_cache` WHERE `chapter_id` = ?');
        $sql->bind_param('i', $id);
        $status = $sql->execute();

        return $status;
    }*/
}
