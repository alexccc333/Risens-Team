<?php

class Logger {
    protected static $_instance; 
    protected $_adapter;
    
    const IP = 'ip';
    const ACTION = 'action';
    const ACTION_LOGIN = 'login';
    const ACTION_EPISODE_UPLOAD = 'upload_episode';
    const ACTION_EPISODE_UPDATE = 'update_episode';
    const ACTION_EPISODE_DELETE = 'delete_episode';
    const ACTION_CHAPTER_UPLOAD = 'upload_chapter';
    const ACTION_CHAPTER_UPDATE = 'update_chapter';
    const ACTION_CHAPTER_DELETE = 'delete_chapter';
    const ACTION_CHAPTER_CLEAR  = 'clear_chapter';
    const ACTION_ANIME_CREATE = 'create_anime';
    const ACTION_ANIME_EDIT = 'edit_anime';
    const ACTION_ANIME_DELETE = 'delete_anime';
    const ACTION_MANGA_CREATE = 'create_manga';
    const ACTION_MANGA_EDIT = 'edit_manga';
    const ACTION_MANGA_DELETE = 'delete_manga';
    const ACTION_USER_CREATE = 'create_user';
    const ACTION_USER_EDIT = 'edit_user';
    const ACTION_USER_DEACTIVATE = 'deactivate_user';
    const ACTION_USER_ACTIVATE = 'activate_user';
    const ACTION_APIKEY_CREATE = 'create_apikey';
    const ACTION_APIKEY_CHANGE = 'change_apikey';
    const ACTION_APIKEY_DELETE = 'delete_apikey';
    const ACTION_APIKEY_ENABLE = 'enable_apikey';
    const ACTION_APIKEY_DISABLE = 'disable_apikey';
    const APIKEY = 'apikey';
    const CONTENT_BEFORE = 'content_before';
    const CONTENT_AFTER = 'content_after';
    const SUBJECT_ID = 'subject_id';
    const STATUS = 'status';
    const STATUS_OK = 'ok';
    const STATUS_FAIL = 'fail';
        
    private function __construct() {        
        
    }
    
    public static function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self;   
        }
 
        return self::$_instance;
    }
    
    public function setAdapter($adapter) {
        $this->_adapter = $adapter;
    }
    
    public function log($userId, $data) {
        $data = $this->_encodeData($data);
        $this->_adapter->insertLog($userId, $data);
    }
    
    protected function _encodeData($data) {
        return json_encode($data);
    }
    
    public function getPage($page) {
        return $this->_adapter->getLogs($page);
    }
    
    public function getPageCount() {
        return $this->_adapter->getRowsCount();
    }
    
    public function getUserNameById($id) {
        return $this->_adapter->getUserNameById($id);
    }
}
