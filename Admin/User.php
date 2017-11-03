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
    
    const LOGGED_SUCCESS = 2;
    const LOGGED_FAILED = 1;
    const NOT_LOGGED = 0;
    
	protected $_isLogged = 0;
	
	public function __construct() {
		$this->_role = UserEnum::ROLE_ANON;
	}
	
    public function login($login, $password, $mysqli) {
        $this->_adapter = new UserDataAdapter($mysqli);
        $status = $this->_tryLogin($login, $password);
        
        if ($status) {
            $toFetch = $this->_adapter->getDataForUser($this->_id);
            $this->_fillUser($toFetch);
            
            $cookie = hash('sha512', $login . time());
            if ($this->_adapter->setCookieToUser($this->_id, $cookie)) {
                $this->_cookie = $cookie;
                setcookie('user_cookie', $cookie, time()+60*60*24*7);
            }
            $this->_isLogged = self::LOGGED_SUCCESS;
        }
        
        $this->_isLogged = self::LOGGED_FAILED;
    }


    public function loginByCookie($hashCookie, $mysqli) {
		$this->_adapter = new UserDataAdapter($mysqli);
        $toFetch = $this->_adapter->getUserByCookie($hashCookie);
        if ($toFetch) {
            $this->_fillUser($toFetch);
            $this->_isLogged = self::LOGGED_SUCCESS;
        }
        
        $this->_isLogged = self::LOGGED_FAILED;
	}
	
	protected function _tryLogin($login, $password) {
		$this->_id = $this->_adapter->checkLoginInformationAndGetId($login, $password);
		if ($this->_id && $this->_id !== 0) {
			return true;
		}
        return false;
	}
	
	protected function _fillUser($toFetch) {
        $this->_id = $toFetch[UserEnum::COL_ID];
		$this->_role = $toFetch[UserEnum::COL_ROLE];
		$this->_name = $toFetch[UserEnum::COL_NAME];
		$this->_availableAnime = explode(',', $toFetch[UserEnum::COL_AVAIL_ANIME]);
		$this->_availableManga = explode(',', $toFetch[UserEnum::COL_AVAIL_MANGA]);
        $this->_cookie = $toFetch[UserEnum::COL_COOKIE];
	}
    
    public function isAnon() {
        return !$this->_id || $this->_role === UserEnum::ROLE_ANON;
    }
    
    public function isLogged() {
        return $this->_isLogged;
    }
    
    public function getRole() {
        return $this->_role;
    }
    
    public function getAdapter() {
        return $this->_adapter;
    }
    
    public function getName() {
        return $this->_name;
    }
}
