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
	
	public function __construct() {
		$this->_role = UserEnum::ROLE_ANON;
	}
	
    public function login($login, $password, $mysqli) {
        $this->_adapter = new UserDataAdapter($mysqli);
        $this->_tryLogin($login, $password);
        
        if ($this->_isLogged) {
            $toFetch = $this->_adapter->getDataForUser($this->_id);
            $this->_fillUser($toFetch);
            
            $cookie = hash('sha512', $login . time());
            if ($this->_adapter->setCookieToUser($this->_id, $cookie)) {
                $this->_cookie = $cookie;
                setcookie('user_cookie', $cookie, time()+60*60*24*7);
            }
        }
        else {
            throw new Exception('Could not login');
        }
    }


    public function loginByCookie($hashCookie, $mysqli) {
		$this->_adapter = new UserDataAdapter($mysqli);
        $toFetch = $this->_adapter->getUserByCookie($hashCookie);
        $this->_fillUser($toFetch);
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
        $this->_cookie = $toFetch[UserEnum::COL_COOKIE];
	}
    
    public function isAnon() {
        return $this->_role === UserEnum::ROLE_ANON;
    }
}
