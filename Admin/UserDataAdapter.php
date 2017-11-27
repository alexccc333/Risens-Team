<?php

class UserDataAdapter extends DataAdapter {	
	public function getDataForUser($id) {
		$sql = $this->_mysqli->prepare('SELECT * FROM users WHERE id =?');
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
	
	public function checkLoginInformationAndGetId($login, $password) {
        $sql = $this->_mysqli->prepare('SELECT id FROM users WHERE `name` =? AND `password` =? AND `active` =1');
        $sql->bind_param('ss', $login, $password);
        $status = $sql->execute();
		
		if (!$status) {
			return 0;
		}
        $result = $sql->get_result();
		$row = $result->fetch_assoc();
        
		return $row['id'];
	}
	
	public function setCookieToUser($id, $cookie) {
        $sql = $this->_mysqli->prepare('UPDATE users SET `cookie` =? WHERE `id` =?');
        $sql->bind_param('si', $cookie, $id);
		$status = $sql->execute();
        
		if (!$status) {
			throw new Exception('Could not update cookie');
		}
		
		return true;
	}
    
    public function createNewUser($login, $passHash, $role, $availAnime, $availManga) {
        $sql = $this->_mysqli->prepare('INSERT INTO `users`(`id`, `name`, `password`, `role`, `available_anime`, `available_manga`, `cookie`) '
                . 'VALUES (NULL,?,?,?,?,?,NULL)');
        $sql->bind_param('ssiss', $login, $passHash, $role, $availAnime, $availManga);
        $status = $sql->execute();
        
        if ($status) {
            return $sql->insert_id;
        }
        else {
            return false;
        }
    }

    public function deactivateUser($id) {
        $sql = $this->_mysqli->prepare('UPDATE users SET `active` =0 WHERE `id` =?');
        $sql->bind_param('i', $id);
        $status = $sql->execute();
        
        return $status;
    }
    
    public function activateUser($id) {
        $sql = $this->_mysqli->prepare('UPDATE users SET `active` =1 WHERE `id` =?');
        $sql->bind_param('i', $id);
        $status = $sql->execute();
        
        return $status;
    }
    
    public function updateUser($id, $login, $passHash, $role, $availAnime, $availManga, $cookie) {
        $sql = $this->_mysqli->prepare('UPDATE `users` SET `name`=?,`password`=?,`role`=?,`available_anime`=?,`available_manga`=?,`cookie`=? WHERE `id`=?');
        $sql->bind_param('ssisssi', $login, $passHash, $role, $availAnime, $availManga, $cookie, $id);
        $status = $sql->execute();
        
        return $status;
    }

    public function getUserByCookie($cookie) {
        $sql = $this->_mysqli->prepare('SELECT * FROM users WHERE `cookie` =? AND `active` =1');
        $sql->bind_param('s', $cookie);
        $status = $sql->execute();
        
        if ($status) {
            $result = $sql->get_result();
			$row = $result->fetch_assoc();
			$result->free();
			
			return $row;
		}
    }
    
    public function getAllUsers() {
        $sql = $this->_mysqli->prepare('SELECT id, name from users');
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
    
    public function getIdByLogin($login) {
        $sql = $this->_mysqli->prepare('SELECT id FROM users WHERE `name` =?');
        $sql->bind_param('s', $login);
        $status = $sql->execute();
		
		if (!$status) {
			return 0;
		}
        $result = $sql->get_result();
		$row = $result->fetch_assoc();
        
		return $row['id'];
    }
    
    public function getAnimeAdapter() {
        return new AnimeDataAdapter($this->_mysqli);
    }
    
    public function getMangaAdapter() {
        return new MangaDataAdapter($this->_mysqli);
    }
    
    public function getEpisodeAdapter() {
        return new EpisodeDataAdapter($this->_mysqli);
    }
    
    public function getChapterAdapter() {
        return new ChapterDataAdapter($this->_mysqli);
    }
    
    public function getYandexDiskAdapter() {
        return new YandexDiskAdapter($this->_mysqli);
    }

    public function getApiAdapter() {
	    return new ApiDataAdapter($this->_mysqli);
    }
}
