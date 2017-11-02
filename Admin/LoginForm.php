<?php
include 'Frame/Main.php';

class LoginForm extends Main {
    protected $_currentUser;
    
    const ROUTE_NAVIGATE = 'navigate';
    const ROUTE_UPLOAD_ANIME = 'uploadanime';
    const ROUTE_UPLOAD_MANGA = 'uploadmanga';
    const ROUTE_UPLOAD_EPISODE = 'uploadepisode';
    const ROUTE_UPLOAD_CHAPTER = 'uploadchapter';
    
    public function setUser($user) {
        $this->_currentUser = $user;
    }

    public function _printError($string) {
        echo $string;
    }

    protected function _printForm() {
        echo '<form method="post" action="adminpanel.php">';
        echo '<input type="text" name="login"/>';
        echo '<input type="password" name="password" />';
        echo '<button>Login</button></form>';
    }
    
    public function _printLogOut() {
        echo '<a href="?logout">Logout</a>';
    }
    
    public function printBody($location = '') {
        echo '<body>';
        if ($this->_currentUser->isAnon()){ 
            $status = $this->_currentUser->isLogged();
            $this->_printForm();
            if ($status === User::LOGGED_FAILED) {
                $this->_printError('Could not login with these login and password');
            }
        }
        else {
            $this->_printLogOut();
            $route = $this->_getRoute();
            
            switch ($route) {
                case self::ROUTE_NAVIGATE:
                default:
                    $this->_printMenu();
            }
        }
        echo '</body>';
    }
    
    protected function _printMenu() {
        echo '<br><h3>';
        echo '<p class="text-center">';
        echo '<a href="?go=' . self::ROUTE_UPLOAD_ANIME . '">Добавить новый тайтл в БД</a><br>';
        echo '<a href="?go=' . self::ROUTE_UPLOAD_EPISODE . '">Добавить новую серию в БД</a><br>';
        echo '<hr><p class="text-center">';
        echo '<a href="?go=' . self::ROUTE_UPLOAD_MANGA . '">Добавить новую мангу в БД</a><br>';
        echo '<a href="?go=' . self::ROUTE_UPLOAD_CHAPTER . '">Добавить новую главу в БД</a><br>';
        echo '</h3>';
    }
    
    protected function _getRoute() {
        if (!isset($_GET['go'])) {
            return self::ROUTE_NAVIGATE;
        }
        else {
            return $_GET['go'];
        }
    }
}
