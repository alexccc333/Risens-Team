<?php
include 'Frame/Main.php';
include 'Admin/AnimeDataAdapter.php';

class Router extends Main {
    protected $_currentUser;
    
    const ROUTE_NAVIGATE = 'navigate';
    const ROUTE_CREATE_ANIME = 'createanime';
    const ROUTE_EDIT_ANIME = 'editanime';
    const ROUTE_CREATE_MANGA = 'createmanga';
    const ROUTE_UPLOAD_EPISODE = 'uploadepisode';
    const ROUTE_UPLOAD_CHAPTER = 'uploadchapter';
    const ROUTE_EDIT_USER_PRIVILEGES = 'edituserprivileges';
    const ROUTE_CREATE_NEW_USER = 'createnewuser';
    
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
        echo '<a href="?logout">Logout from ' . $this->_currentUser->getName() . '</a><br>';
    }
    
    public function _printBackLink() {
         echo '<a href="?go=' . self::ROUTE_NAVIGATE . '">К меню</a>';
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
            if ($route !== self::ROUTE_NAVIGATE) {
                $this->_printBackLink();
            }
            if (!$this->_checkRole($route)) {
                $this->_printError('<br>You can\'t be here');
                return;
            }
            
            switch ($route) {
                case self::ROUTE_CREATE_NEW_USER:
                    $this->_printCreateNewUserMenu();
                    break;
                case self::ROUTE_EDIT_USER_PRIVILEGES:
                    $this->_printEditUserMenu();
                    break;
                case self::ROUTE_CREATE_ANIME:
                    $this->_printNewAnimeMenu();
                    break;
                case self::ROUTE_EDIT_ANIME:
                    $this->_printEditAnimeMenu();
                    break;
                case self::ROUTE_NAVIGATE:
                default:
                    $this->_printMenu();
            }
        }
        
        echo '</body>';
    }
    
    protected function _checkRole($route) {
        $role = $this->_currentUser->getRole();
        
        switch ($route) {
            case self::ROUTE_CREATE_NEW_USER:
            case self::ROUTE_EDIT_USER_PRIVILEGES:
                if ($role !== UserEnum::ROLE_MEGA_ADMIN) {
                    return false;
                }
                break;
            case self::ROUTE_CREATE_ANIME:
            case self::ROUTE_EDIT_ANIME:
            case self::ROUTE_CREATE_MANGA:
                if ($role === UserEnum::ROLE_MANAGER) {
                    return false;
                }
                break;
            case self::ROUTE_UPLOAD_EPISODE:
            case self::ROUTE_UPLOAD_CHAPTER:
                if ($role !== UserEnum::ROLE_ANON) {
                    return false;
                }
        }
        
        return true;
    }
    
    protected function _printMenu() {
        echo '<br><h3>';
        echo '<p class="text-center">';
        switch ($this->_currentUser->getRole()) {
            case UserEnum::ROLE_MEGA_ADMIN:
                echo '<a href="?go=' . self::ROUTE_CREATE_NEW_USER . '">Создание нового пользователя</a><br>';
                echo '<a href="?go=' . self::ROUTE_EDIT_USER_PRIVILEGES . '">Редактировать привилегии пользователей</a><br>';
                echo '</p><hr>';
            case UserEnum::ROLE_ADMIN:
                echo '<p class="text-center">';
                echo '<a href="?go=' . self::ROUTE_CREATE_ANIME . '">Добавить новый тайтл в БД</a><br>';
                echo '<a href="?go=' . self::ROUTE_EDIT_ANIME . '">Редактировать тайтл</a><br>';
                echo '<a href="?go=' . self::ROUTE_CREATE_MANGA . '">Добавить новую мангу в БД</a><br>';
                echo '</p><hr>';
            case UserEnum::ROLE_MANAGER:
                echo '<p class="text-center">';
                echo '<a href="?go=' . self::ROUTE_UPLOAD_EPISODE . '">Добавить новую серию в БД</a><br>';
                echo '<a href="?go=' . self::ROUTE_UPLOAD_CHAPTER . '">Добавить новую главу в БД</a><br>';
                break;
            default:
                echo 'Could not identify user';
        }
        echo '</p></h3>';
    }
    
    protected function _printCreateNewUserMenu() {
        if (isset($_POST['new_login']) && isset($_POST['new_password']) && isset($_POST['role'])) {
            $adapter = $this->_currentUser->getAdapter();
            $password = hash('sha512', $_POST['new_password'] . SALT);
            $availAnime = isset($_POST['availAnime']) ? $_POST['availAnime'] : '';
            $availManga = isset($_POST['availManga']) ? $_POST['availManga'] : '';
            $status = $adapter->createNewUser($_POST['new_login'], $password, $_POST['role'], $availAnime, $availManga);
            
            if ($status) {
                echo '<h3><p class="text-center">User created</p></h3>';
            }
            else {
                echo '<h3><p class="text-center">Could not create user</p></h3>';
            }
        }
        else {
            echo '<form action="adminpanel.php?go=' . self::ROUTE_CREATE_NEW_USER . '" method="post">';
            echo 'Логин: <input type="text" required name="new_login" id="login" /><br>';
            echo 'Пароль: <input type="password" required name="new_password" id="password" /><br>';
            echo 'Роль: <input type="text" required name="role" id="role" /><br>';
            echo 'Разрешенные для редактирования аниме: <input type="text" name="availAnime" id="availAnime" /><br>';
            echo 'Разрешенная для редактирования манга: <input type="text" name="availManga" id="availManga" /><br>';
            echo '<input type="submit" value="Submit">';
            echo '</form>';
        }
    }
    
    protected function _printEditUserMenu() {
        $adapter = $this->_currentUser->getAdapter();
        
        $id = isset($_GET['set_id']) ? intval($_GET['set_id']) : 0;
        if ($id === 0) {
            $users = $adapter->getAllUsers();
            echo '<br><h3>';
            echo '<p class="text-center">';
            foreach ($users as $user) {
                echo '<a href="adminpanel.php?go=' . self::ROUTE_EDIT_USER_PRIVILEGES . '&set_id=' . $user['id'] . '">'
                        . $user[UserEnum::COL_ID] . ' — ' . $user[UserEnum::COL_NAME] . '</a><br>';
            }
            echo '</p></h3>';
        }
        else {
            $user = $adapter->getDataForUser($id);
            if (isset($_POST['new_login']) && isset($_POST['new_password']) && isset($_POST['role'])) {
                $user[UserEnum::COL_NAME] = $_POST['new_login'];
                if ($_POST['new_password'] !== '') {
                    $user[UserEnum::COL_PASSWORD] = hash('sha512', $_POST['new_password'] . SALT);
                }
                $user[UserEnum::COL_ROLE] = $_POST['role'];
                if (isset($_POST['availAnime'])) {
                    $user[UserEnum::COL_AVAIL_ANIME] = $this->_clearNewIdList($_POST['availAnime']);
                }
                if (isset($_POST['availManga'])) {
                    $user[UserEnum::COL_AVAIL_MANGA] = $this->_clearNewIdList($_POST['availManga']);
                }
                
                $adapter->updateUser($id, $user[UserEnum::COL_NAME],  $user[UserEnum::COL_PASSWORD], $user[UserEnum::COL_ROLE], $user[UserEnum::COL_AVAIL_ANIME], $user[UserEnum::COL_AVAIL_MANGA]);
            }
            elseif (isset($_GET['delete_user'])) {
                if ($user[UserEnum::COL_ROLE] !== UserEnum::ROLE_MEGA_ADMIN) {
                    if ($adapter->deactivateUser($id)) {
                        $user[UserEnum::COL_ACTIVE] = 0;
                        echo '<br>Пользователь успешно отключен';
                    }
                }
            }
            elseif (isset($_GET['activate_user'])) {
                if ($adapter->activateUser($id)) {
                    $user[UserEnum::COL_ACTIVE] = 1;
                    echo '<br>Пользователь успешно восстановлен';
                }
            }

            echo '<br><a href="?go=' . self::ROUTE_EDIT_USER_PRIVILEGES . '">Назад</a>';
            echo '<form action="adminpanel.php?go=' . self::ROUTE_EDIT_USER_PRIVILEGES . '&set_id=' . $id . '" method="post">';
            echo 'Логин: <input type="text" required name="new_login" id="login" value="' . $user[UserEnum::COL_NAME] . '" /><br>';
            echo 'Пароль: <input type="password" name="new_password" id="password" /><br>';
            echo 'Роль: <input type="text" required name="role" id="role" value="' . $user[UserEnum::COL_ROLE] . '" /> (1 - менеджер, 2 - админ, 3 - мега админ)<br>';
            echo 'Разрешенные для редактирования аниме: <input type="text" name="availAnime" id="availAnime" value="' . $user[UserEnum::COL_AVAIL_ANIME] . '" />(айдишники через запятую)<br>';
            echo 'Разрешенная для редактирования манга: <input type="text" name="availManga" id="availManga" value="' . $user[UserEnum::COL_AVAIL_MANGA] . '" />(айдишники через запятую)<br>';
            echo '<input type="submit" value="Submit"><br>';
            if ($user[UserEnum::COL_ACTIVE]) {
                echo '<a href="adminpanel.php?go=' . self::ROUTE_EDIT_USER_PRIVILEGES . '&set_id=' . $id . '&delete_user">Удалить</a>';
            }
            else {
                echo '<a href="adminpanel.php?go=' . self::ROUTE_EDIT_USER_PRIVILEGES . '&set_id=' . $id . '&activate_user">Восстановить</a>';
            }
            echo '</form>';
        }
    }
    
    protected function _printNewAnimeMenu() {
        $adapter = $this->_currentUser->getAdapter()->getAnimeAdapter();
        
        if (isset($_POST['anime_name'])) {
            $status = $adapter->createNewAnime($_POST['anime_name'], $_POST['banner_url']);
            
            if ($status) {
                echo '<h3><p class="text-center">Anime created</p></h3>';
            }
            else {
                echo '<h3><p class="text-center">Could not create anime</p></h3>';
            }
        }
        
        echo '<form action="adminpanel.php?go=' . self::ROUTE_CREATE_ANIME . '" method="post">';
        echo 'Название аниме (англ.): <input type="text" required name="anime_name" id="name" /><br>';
        echo 'Полный путь до баннера: <input type="text" name="banner_url" id="banner" value="http://risens.team/uploads/" /><br>';
        echo '<input type="submit" value="Submit">';
        echo '</form>';
    }
    
    protected function _printEditAnimeMenu() {
        $adapter = $this->_currentUser->getAdapter()->getAnimeAdapter();
        
        $id = isset($_GET['set_id']) ? intval($_GET['set_id']) : 0;
        if ($id === 0) {
            $animes = $adapter->getAllAnimes();
            echo '<br><h3>';
            echo '<p class="text-center">';
            foreach ($animes as $anime) {
                echo '<a href="adminpanel.php?go=' . self::ROUTE_EDIT_ANIME . '&set_id=' . $anime['id'] . '">'
                        . $anime[UserEnum::COL_ID] . ' — ' . $anime[UserEnum::COL_NAME] . '</a><br>';
            }
            echo '</p></h3>';
        }
        else {
            $anime = $adapter->getAnimeById($id);
            
            if (isset($_POST['anime_name'])) {
                $anime[AnimeDataAdapter::COL_NAME] = $_POST['anime_name'];
                $anime[AnimeDataAdapter::COL_BANNER] = $_POST['banner_url'];
                
                $adapter->updateAnime($id, $anime[AnimeDataAdapter::COL_NAME], $anime[AnimeDataAdapter::COL_BANNER]);
            }
            
            echo '<br><a href="?go=' . self::ROUTE_EDIT_ANIME . '">Назад</a>';
            echo '<form action="adminpanel.php?go=' . self::ROUTE_EDIT_ANIME . '&set_id=' . $id . '" method="post">';
            echo 'Название аниме (англ.): <input type="text" required name="anime_name" id="name" value="' . $anime[AnimeDataAdapter::COL_NAME] . '" /><br>';
            echo 'Полный путь до баннера: <input type="text" name="banner_url" id="banner" value="' . $anime[AnimeDataAdapter::COL_BANNER] . '" /><br>';
            echo '<input type="submit" value="Submit"><br>';
            /*if ($user[UserEnum::COL_ACTIVE]) {
                echo '<a href="adminpanel.php?go=' . self::ROUTE_EDIT_USER_PRIVILEGES . '&set_id=' . $id . '&delete_user">Удалить</a>';
            }
            else {
                echo '<a href="adminpanel.php?go=' . self::ROUTE_EDIT_USER_PRIVILEGES . '&set_id=' . $id . '&activate_user">Восстановить</a>';
            }*/
            echo '</form>';
        }
    }
    
    protected function _getRoute() {
        if (!isset($_GET['go'])) {
            return self::ROUTE_NAVIGATE;
        }
        else {
            return $_GET['go'];
        }
    }
    
    protected function _clearNewIdList($list) {
        $list = explode(',', $list);
        $list = array_filter($list, function($element) {
            return !empty($element) && !ctype_space($element);
        });
        foreach ($list as $key => $id) {
            $list[$key] = intval($id);
        }
        
        return implode(',', $list);
    }
}
