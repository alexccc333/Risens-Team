<?php

class UserDataAdapter {
	protected $_mysqli = null;
		
	public function __construct($mysqli) {
		$this->_mysqli = $mysqli;
	}
	
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
        $sql = $this->_mysqli->prepare('SELECT id FROM users WHERE `name` =? AND `password` =?');
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
    
    public function getUserByCookie($cookie) {
        $sql = $this->_mysqli->prepare('SELECT * FROM users WHERE `cookie` =?');
        $sql->bind_param('s', $cookie);
        $status = $sql->execute();
        
        if ($status) {
            $result = $sql->get_result();
			$row = $result->fetch_assoc();
			$result->free();
			
			return $row;
		}
    }
}
