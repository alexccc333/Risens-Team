<?php
include_once 'DataAdapter.php';

class AnimeDataAdapter extends DataAdapter {
    const COL_ID = 'id';
    const COL_NAME = 'name';
    const COL_BANNER = 'poster';
    const COL_REDIRECT = 'redirect';

    public function getAllAnimes() {
        $sql = $this->_mysqli->prepare('SELECT id, name from anime ORDER BY id DESC');
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

    public function getAnimeById($id) {
        $sql = $this->_mysqli->prepare('SELECT name, poster, redirect from anime WHERE id=?');
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

    public function updateAnime($id, $name, $bannerUrl, $redirectUrl) {
        $sql = $this->_mysqli->prepare('UPDATE `anime` SET `name`=?,`poster`=?,`redirect`=? WHERE `id`=?');
        $sql->bind_param('sssi', $name, $bannerUrl, $redirectUrl, $id);
        $status = $sql->execute();

        return $status;
    }

    public function createNewAnime($name, $bannerUrl, $redirectUrl) {
        $sql = $this->_mysqli->prepare('INSERT INTO `anime`(`id`, `name`, `poster`,`redirect`) VALUES(NULL,?,?,?)');
        $sql->bind_param('sss', $name, $bannerUrl, $redirectUrl);
        $status = $sql->execute();

        if ($status) {
            return $sql->insert_id;
        }
        else {
            return false;
        }
    }

    public function removeAnime($id) {
        $sql = $this->_mysqli->prepare('DELETE FROM `anime` WHERE `id` = ?');
        $sql->bind_param('i', $id);
        $status = $sql->execute();

        return $status;
    }
}
