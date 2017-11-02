<?php

class UserDataAdapter {
	protected $_mysqli = null;
		
	public function __construct($mysqli) {
		$this->_mysqli = $mysqli;
	}
	
	public function getDataForUser($id) {
		$sql = 'SELECT * FROM users WHERE id = ' . $id;
		$result = $this->_mysqli->query($sql);
		
		if ($result) {
			$row = $result->fetch_assoc();
			$result->free();
			
			return $row;
		}
		return false;
	}
	
	public function checkLoginInformationAndGetId($login, $password) {
		$sql = 'SELECT id FROM users WHERE `name` = \'' . $login . '\' AND `password` = \'' . $password . '\';';
		$result = $this->_mysqli->query($sql);
		if (!$result) {
			return 0;
		}
		
		$row = $result->fetch_assoc();
		
		return $row['id'];
	}
	
	
	
	public function setCookieToUser($id, $cookie) {
		$sql = 'UPDATE users SET `cookie` = \'' . $cookie . '\' WHERE `id` = \'' . $id . '\';';
		$result = $this->_mysqli->query($sql);
		
		if (!$result) {
			throw new Exception('Could not update cookie');
		}
		
		return true;
	}
}
