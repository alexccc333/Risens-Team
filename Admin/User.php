<?php
include 'Admin\UserDataAdapter.php';
include 'Admin\UserRoles.php';

class User {
	protected $_id = 0;
	protected $_role = 0;
	protected $_name = '';
	protected $_cookie = '';


	protected $_availableAnime = array();
	protected $_availableManga = array();
	
	protected $_adapter = null;
	protected $_isLogged = false;
	
	public function __construct($login, $password, $mysqli, $cookie) {
		$this->_adapter = new UserDataAdapter($mysqli);
		$this->_tryLogin($login, $password);		
		
		if ($this->_isLogged) {
			$toFetch = $this->_adapter->getDataForUser($this->_id);
			$this->_fillUser($toFetch);
			
			if ($this->_adapter->setCookieToUser($this->_id, $cookie)) {
				$this->_cookie = $cookie;
			}
		}
		else {
			throw new Exception('Could not login');
		}
	}
	
	public static function loginByCookie($hashCookie, $mysqli) {
		
	}
	
	protected function _tryLogin($login, $password) {
		$this->_id = $this->_adapter->checkLoginInformationAndGetId($login, $password);
		if ($this->_id !== 0) {
			$this->_isLogged = true;
		}
	}
	
	protected function _fillUser($toFetch) {
		$this->_role = $toFetch[UserEnum::COL_ROLE];
		$this->_name = $toFetch[UserEnum::COL_NAME];
		$this->_availableAnime = explode(',', $toFetch[UserEnum::COL_AVAIL_ANIME]);
		$this->_availableManga = explode(',', $toFetch[UserEnum::COL_AVAIL_MANGA]);
	}
}
