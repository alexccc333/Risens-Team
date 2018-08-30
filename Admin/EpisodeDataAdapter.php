<?php
include_once 'DataAdapter.php';

class EpisodeDataAdapter extends DataAdapter {
    const COL_ID = 'id';
    const COL_ANIME_ID = 'anime_id';
    const COL_NUMBER = 'number';
    const COL_NAME = 'name';
    const COL_SUB_VIDEO_ID = 'subvideoid';
    const COL_SUB_PATH = 'subpath';
    const COL_DUB_VIDEO_ID = 'dubvideoid';
    const COL_DUB_SUB_PATH = 'dubsubpath';

    public function getEpisodesByAnimeId($animeId, $getSubs = false) {
      $cols = $getSubs ? self::COL_ID . ',' . self::COL_NUMBER . ',' . self::COL_NAME . ',' . self::COL_SUB_VIDEO_ID .
        ',' . self::COL_SUB_PATH . ',' . self::COL_DUB_VIDEO_ID . ',' . self::COL_DUB_SUB_PATH :
        self::COL_ID . ',' . self::COL_NAME;
      $sql = $this->_mysqli->prepare('SELECT ' . $cols . ' from episodes WHERE anime_id=?');
      $sql->bind_param('i', $animeId);
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

    public function getEpisodeById($id) {
        $sql = $this->_mysqli->prepare('SELECT * from episodes WHERE id=?');
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

    public function updateEpisode($id, $name, $number, $subVideoId, $subPath, $dubVideoId, $dubPath) {
        $sql = $this->_mysqli->prepare('UPDATE `episodes` SET `name`=?,`number`=?,`subvideoid`=?,`subpath`=?,`dubvideoid`=?,`dubsubpath`=? WHERE `id`=?');
        $sql->bind_param('ssssssi', $name, $number, $subVideoId, $subPath, $dubVideoId, $dubPath, $id);
        $status = $sql->execute();

        return $status;
    }

    public function removeEpisode($id) {
        $sql = $this->_mysqli->prepare('DELETE FROM `episodes` WHERE `id` = ?');
        $sql->bind_param('i', $id);
        $status = $sql->execute();

        return $status;
    }

    public function createNewEpisode($name, $number, $animeId, $subVideoId, $subPath, $dubVideoId, $dubPath) {
        $sql = $this->_mysqli->prepare('INSERT INTO `episodes`(`id`, `name`, `number`, `anime_id`, `subvideoid`, `subpath`, `dubvideoid`, `dubsubpath`) VALUES(NULL,?,?,?,?,?,?,?)');
        $sql->bind_param('ssissss', $name, $number, $animeId, $subVideoId, $subPath, $dubVideoId, $dubPath);
        $status = $sql->execute();

        if ($status) {
            return $sql->insert_id;
        }
        else {
            return false;
        }
    }
}
