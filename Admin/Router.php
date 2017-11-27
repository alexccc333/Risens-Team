<?php
include 'Frame/Main.php';
include 'Admin/AnimeDataAdapter.php';
include 'Admin/MangaDataAdapter.php';
include 'Admin/EpisodeDataAdapter.php';
include 'Admin/ChapterDataAdapter.php';
include 'Admin/MangaCacheDataAdapter.php';
include 'Admin/YandexDiskAdapter.php';
include 'Admin/ApiDataAdapter.php';

class Router extends Main {
    protected $_currentUser;
    
    const ROUTE_NAVIGATE = 'navigate';
    const ROUTE_CREATE_ANIME = 'createanime';
    const ROUTE_EDIT_ANIME = 'editanime';
    const ROUTE_CREATE_MANGA = 'createmanga';
    const ROUTE_EDIT_MANGA = 'editmanga';
    const ROUTE_UPLOAD_EPISODE = 'uploadepisode';
    const ROUTE_UPLOAD_CHAPTER = 'uploadchapter';
    const ROUTE_EDIT_USER_PRIVILEGES = 'edituserprivileges';
    const ROUTE_CREATE_NEW_USER = 'createnewuser';
    const ROUTE_SHOW_LOGS = 'showlogs';
    const ROUTE_API_KEYS = 'apikeys';
    const ROUTE_API_KEYS_MANAGE = 'manageapikeys';
    const ROUTE_API_KEYS_UPDATE = 'updatekeysdata';
    
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
                case self::ROUTE_SHOW_LOGS:
                    $this->_showLogs();
                    break;
                case self::ROUTE_API_KEYS:
                    $this->_setApiKeys();
                    break;
                case self::ROUTE_API_KEYS_MANAGE:
                    $this->_manageApiKeys();
                    break;
                case self::ROUTE_API_KEYS_UPDATE:
                    $this->_updateKeyData();
                    break;
                case self::ROUTE_CREATE_ANIME:
                    $this->_printNewAnimeMenu();
                    break;
                case self::ROUTE_EDIT_ANIME:
                    $this->_printEditAnimeMenu();
                    break;
                case self::ROUTE_CREATE_MANGA:
                    $this->_printNewMangaMenu();
                    break;
                case self::ROUTE_EDIT_MANGA:
                    $this->_printEditMangaMenu();
                    break;
                case self::ROUTE_UPLOAD_EPISODE:
                    $this->_printUploadEpisodeMenu();
                    break;
                case self::ROUTE_UPLOAD_CHAPTER:
                    $this->_printUploadChapterMenu();
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
            case self::ROUTE_SHOW_LOGS:
                if ($role !== UserEnum::ROLE_MEGA_ADMIN) {
                    return false;
                }
                break;
            case self::ROUTE_CREATE_ANIME:
            case self::ROUTE_EDIT_ANIME:
            case self::ROUTE_CREATE_MANGA:
            case self::ROUTE_EDIT_MANGA:
                if ($role === UserEnum::ROLE_MANAGER) {
                    return false;
                }
                break;
            case self::ROUTE_UPLOAD_EPISODE:
            case self::ROUTE_UPLOAD_CHAPTER:
                if ($role === UserEnum::ROLE_ANON) {
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
                echo '<a href="?go=' . self::ROUTE_SHOW_LOGS . '">Посмотреть логи</a><br>';
                echo '<a href="?go=' . self::ROUTE_API_KEYS . '">Создание ключа для API</a><br>';
                echo '<a href="?go=' . self::ROUTE_API_KEYS_MANAGE . '">Управление API ключами</a><br>';
                echo '</p><hr>';
            case UserEnum::ROLE_ADMIN:
                echo '<p class="text-center">';
                echo '<a href="?go=' . self::ROUTE_CREATE_ANIME . '">Добавить новый тайтл в БД</a><br>';
                echo '<a href="?go=' . self::ROUTE_EDIT_ANIME . '">Редактировать тайтл</a><br>';
                echo '<a href="?go=' . self::ROUTE_CREATE_MANGA . '">Добавить новую мангу в БД</a><br>';
                echo '<a href="?go=' . self::ROUTE_EDIT_MANGA . '">Редактировать мангу</a><br>';
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
            $id = $adapter->createNewUser($_POST['new_login'], $password, $_POST['role'], $availAnime, $availManga);
            
            $logArray = array(
                        Logger::IP => $this->_getCurrentUserIp(),
                        Logger::ACTION => Logger::ACTION_USER_CREATE,
                        Logger::SUBJECT_ID => $id,
                );
            
            if ($id) {
                $logArray[Logger::STATUS] = Logger::STATUS_OK;
                echo '<h3><p class="text-center">User created</p></h3>';
            }
            else {
                $logArray[Logger::STATUS] = Logger::STATUS_FAIL;
                echo '<h3><p class="text-center">Could not create user</p></h3>';
            }
            
            $userId = $this->_currentUser->getId();
            Logger::getInstance()->log($userId, $logArray);
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
                $contentBefore = $user;
                
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
                
                $logArray = array(
                        Logger::IP => $this->_getCurrentUserIp(),
                        Logger::ACTION => Logger::ACTION_USER_EDIT,
                        Logger::STATUS => Logger::STATUS_OK,
                        Logger::SUBJECT_ID => $id,
                        Logger::CONTENT_BEFORE => $contentBefore,
                        Logger::CONTENT_AFTER => $user,
                );
                $userId = $this->_currentUser->getId();
                Logger::getInstance()->log($userId, $logArray);
                
                $adapter->updateUser($id, $user[UserEnum::COL_NAME],  $user[UserEnum::COL_PASSWORD], $user[UserEnum::COL_ROLE], $user[UserEnum::COL_AVAIL_ANIME], $user[UserEnum::COL_AVAIL_MANGA], $user[UserEnum::COL_COOKIE]);
            }
            elseif (isset($_GET['delete_user'])) {
                if ($user[UserEnum::COL_ROLE] !== UserEnum::ROLE_MEGA_ADMIN) {
                    if ($adapter->deactivateUser($id)) {
                        $logArray = array(
                                Logger::IP => $this->_getCurrentUserIp(),
                                Logger::ACTION => Logger::ACTION_USER_DEACTIVATE,
                                Logger::STATUS => Logger::STATUS_OK,
                                Logger::SUBJECT_ID => $id,
                        );
                        $userId = $this->_currentUser->getId();
                        Logger::getInstance()->log($userId, $logArray);
                        
                        $user[UserEnum::COL_ACTIVE] = 0;
                        echo '<br>Пользователь успешно отключен';
                    }
                }
            }
            elseif (isset($_GET['activate_user'])) {
                if ($adapter->activateUser($id)) {
                    $logArray = array(
                            Logger::IP => $this->_getCurrentUserIp(),
                            Logger::ACTION => Logger::ACTION_USER_ACTIVATE,
                            Logger::STATUS => Logger::STATUS_OK,
                            Logger::SUBJECT_ID => $id,
                    );
                    $userId = $this->_currentUser->getId();
                    Logger::getInstance()->log($userId, $logArray);
                    
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
            echo '<input type="submit" value="Submit"><hr>';
            if ($user[UserEnum::COL_ACTIVE]) {
                echo '<a href="adminpanel.php?go=' . self::ROUTE_EDIT_USER_PRIVILEGES . '&set_id=' . $id . '&delete_user">Удалить</a>';
            }
            else {
                echo '<a href="adminpanel.php?go=' . self::ROUTE_EDIT_USER_PRIVILEGES . '&set_id=' . $id . '&activate_user">Восстановить</a>';
            }
            echo '</form>';
        }
    }

    protected function _setApiKeys() {

        if(isset($_POST['user_id'])) {

            $adapter = $this->_currentUser->getAdapter()->getApiAdapter();
            $reg = $adapter->registerKey();

        } else {

            echo '<form action="adminpanel.php?go=' . self::ROUTE_API_KEYS . '" method="post">';
            echo '<br>User ID: <input type="text" name="user_id" id="uid">';
            echo '<br><input type="submit" value="Send">';
            echo '<br></form>';

        }

    }

    protected function _manageApiKeys() {

        $adapter = $this->_currentUser->getAdapter()->getApiAdapter();

        $keys = $adapter->getUserKeys();
        foreach($keys as $key) {
            echo "<br>" .
                "<form action='adminpanel.php?go=" . self::ROUTE_API_KEYS_UPDATE . "' method='post'>" . "<br>" .
                '№ '. $key['id'] . "<input name='id' type='hidden' value='" . $key['id'] . "'><br>" .
                " User ID: " . "<input name='user_id' type='text' id='uid' value='" .$key['user_id'] . "'><br><br>" .
                " Old API Key: " . $key['api_key'] . "<br>".
                "Сгенерировать ключ заново? <input name='new_key' type='checkbox'>"  . "<br>".
                " Status: <input name='status' type='radio' value='1'" . (isset($key['active']) && $key['active'] == 1 ? "checked='checked'" : false ).">Активен " .
                "<input name='status' type='radio' value='0'" . (isset($key['active']) && $key['active'] == 0 ? "checked='checked'" : false ) . ">Неактивен" . "<br>" .
                "<input name='submit' type='submit' value='Submit changes'>" ."</form><br><br>";
        }

    }

    protected function _updateKeyData() {

        $adapter = $this->_currentUser->getAdapter()->getApiAdapter();

        $update = $adapter->updateData();

    }
    
    protected function _printNewAnimeMenu() {
        $adapter = $this->_currentUser->getAdapter()->getAnimeAdapter();
        
        if (isset($_POST['anime_name'])) {
            $animeId = $adapter->createNewAnime($_POST['anime_name'], $_POST['banner_url']);
            
            $logArray = array(
                        Logger::IP => $this->_getCurrentUserIp(),
                        Logger::ACTION => Logger::ACTION_ANIME_CREATE,
                        Logger::SUBJECT_ID => $animeId,
                );
            
            if ($animeId) {
                $logArray[Logger::STATUS] = Logger::STATUS_OK;
                echo '<h3><p class="text-center">Anime created</p></h3>';
            }
            else {
                $logArray[Logger::STATUS] = Logger::STATUS_FAIL;
                echo '<h3><p class="text-center">Could not create anime</p></h3>';
            }
                
            $userId = $this->_currentUser->getId();
            Logger::getInstance()->log($userId, $logArray);
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
                $contentBefore = $anime;
                $anime[AnimeDataAdapter::COL_NAME] = $_POST['anime_name'];
                $anime[AnimeDataAdapter::COL_BANNER] = $_POST['banner_url'];
                
                $logArray = array(
                        Logger::IP => $this->_getCurrentUserIp(),
                        Logger::ACTION => Logger::ACTION_ANIME_EDIT,
                        Logger::STATUS => Logger::STATUS_OK,
                        Logger::SUBJECT_ID => $id,
                        Logger::CONTENT_BEFORE => $contentBefore,
                        Logger::CONTENT_AFTER => $anime,
                );
                $userId = $this->_currentUser->getId();
                Logger::getInstance()->log($userId, $logArray);
                
                $adapter->updateAnime($id, $anime[AnimeDataAdapter::COL_NAME], $anime[AnimeDataAdapter::COL_BANNER]);
            }
            elseif (isset($_GET['delete'])) {
                $logArray = array(
                        Logger::IP => $this->_getCurrentUserIp(),
                        Logger::ACTION => Logger::ACTION_ANIME_DELETE,
                        Logger::SUBJECT_ID => $id,
                );
                
                if ($adapter->removeAnime($id)) {
                    $logArray[Logger::STATUS] = Logger::STATUS_OK;
                    echo '<script>';
                    echo 'alert("Аниме удалено");';
                    echo 'window.location.replace("?go=' . self::ROUTE_EDIT_ANIME . '");';
                    echo '</script>';
                }
                else {
                    $logArray[Logger::STATUS] = Logger::STATUS_FAIL;
                    $this->_printError('Не удалось удалить');
                }
                
                $userId = $this->_currentUser->getId();
                Logger::getInstance()->log($userId, $logArray);
            }
            
            echo '<br><a href="?go=' . self::ROUTE_EDIT_ANIME . '">Назад</a>';
            echo '<form action="adminpanel.php?go=' . self::ROUTE_EDIT_ANIME . '&set_id=' . $id . '" method="post">';
            echo 'Название аниме (англ.): <input type="text" required name="anime_name" id="name" value="' . $anime[AnimeDataAdapter::COL_NAME] . '" /><br>';
            echo 'Полный путь до баннера: <input type="text" name="banner_url" id="banner" value="' . $anime[AnimeDataAdapter::COL_BANNER] . '" /><br>';
            echo '<input type="submit" value="Submit"><hr>';
            echo '<a href="adminpanel.php?go=' . self::ROUTE_EDIT_ANIME . '&set_id=' . $id . '&delete">Удалить</a>';
            echo '</form>';
        }
    }
    
    protected function _printNewMangaMenu() {
        $adapter = $this->_currentUser->getAdapter()->getMangaAdapter();
        
        if (isset($_POST['manga_name'])) {
            $mangaId = $adapter->createNewManga($_POST['manga_name'], $_POST['folder']);
            
            $logArray = array(
                        Logger::IP => $this->_getCurrentUserIp(),
                        Logger::ACTION => Logger::ACTION_MANGA_CREATE,
                        Logger::SUBJECT_ID => $mangaId,
                );
            
            if ($mangaId) {
                $logArray[Logger::STATUS] = Logger::STATUS_OK;
                echo '<h3><p class="text-center">Manga created</p></h3>';
            }
            else {
                $logArray[Logger::STATUS] = Logger::STATUS_FAIL;
                echo '<h3><p class="text-center">Could not create manga</p></h3>';
            }
            
            $id = $this->_currentUser->getId();
            Logger::getInstance()->log($id, $logArray);
        }
        
        echo '<form action="adminpanel.php?go=' . self::ROUTE_CREATE_MANGA . '" method="post">';
        echo 'Название манги (англ.): <input type="text" required name="manga_name" id="name" /><br>';
        echo 'Папка манги: <input type="text" name="folder" id="folder" /><br>';
        echo '<input type="submit" value="Submit">';
        echo '</form>';
    }

    protected function _printEditMangaMenu() {
        $adapter = $this->_currentUser->getAdapter()->getMangaAdapter();
        
        $id = isset($_GET['set_id']) ? intval($_GET['set_id']) : 0;
        if ($id === 0) {
            $mangas = $adapter->getAllMangas();
            echo '<br><h3>';
            echo '<p class="text-center">';
            foreach ($mangas as $manga) {
                echo '<a href="adminpanel.php?go=' . self::ROUTE_EDIT_MANGA . '&set_id=' . $manga['id'] . '">'
                        . $manga[UserEnum::COL_ID] . ' — ' . $manga[UserEnum::COL_NAME] . '</a><br>';
            }
            echo '</p></h3>';
        }
        else {
            $manga = $adapter->getMangaById($id);
            
            if (isset($_POST['manga_name'])) {
                $contentBefore = $manga;
                $manga[MangaDataAdapter::COL_NAME] = $_POST['manga_name'];
                $manga[MangaDataAdapter::COL_FOLDER] = $_POST['folder'];
                
                $adapter->updateManga($id, $manga[MangaDataAdapter::COL_NAME], $manga[MangaDataAdapter::COL_FOLDER]);
                
                $logArray = array(
                        Logger::IP => $this->_getCurrentUserIp(),
                        Logger::ACTION => Logger::ACTION_MANGA_EDIT,
                        Logger::STATUS => Logger::STATUS_OK,
                        Logger::SUBJECT_ID => $id,
                        Logger::CONTENT_BEFORE => $contentBefore,
                        Logger::CONTENT_AFTER => $manga,
                );
                
                $userId = $this->_currentUser->getId();
                Logger::getInstance()->log($userId, $logArray);
            }
            elseif (isset($_GET['delete'])) {
                $logArray = array(
                        Logger::IP => $this->_getCurrentUserIp(),
                        Logger::ACTION => Logger::ACTION_MANGA_DELETE,
                        Logger::SUBJECT_ID => $id,
                );
                
                if ($adapter->removeManga($id)) {
                    $logArray[Logger::STATUS] = Logger::STATUS_OK;
                    echo '<script>';
                    echo 'alert("Манга удалена");';
                    echo 'window.location.replace("?go=' . self::ROUTE_EDIT_MANGA . '");';
                    echo '</script>';
                }
                else {
                    $logArray[Logger::STATUS] = Logger::STATUS_FAIL;
                    $this->_printError('Не удалось удалить');
                }
                
                $userId = $this->_currentUser->getId();
                Logger::getInstance()->log($userId, $logArray);
            }
            
            echo '<br><a href="?go=' . self::ROUTE_EDIT_MANGA . '">Назад</a>';
            echo '<form action="adminpanel.php?go=' . self::ROUTE_EDIT_MANGA . '&set_id=' . $id . '" method="post">';
            echo 'Название манги (англ.): <input type="text" required name="manga_name" id="name" value="' . $manga[MangaDataAdapter::COL_NAME] . '" /><br>';
            echo 'Папка манги: <input type="text" name="folder" id="folder" value="' . $manga[MangaDataAdapter::COL_FOLDER] . '" /><br>';
            echo '<input type="submit" value="Submit"><hr>';
            echo '<a href="adminpanel.php?go=' . self::ROUTE_EDIT_MANGA . '&set_id=' . $id . '&delete">Удалить</a>';
            echo '</form>';
        }
    }
    
    protected function _printUploadEpisodeMenu() {
        $adapter = $this->_currentUser->getAdapter()->getAnimeAdapter();
        $availAnime = $this->_currentUser->getAvailAnime();
        
        $animeId = isset($_GET['set_anime_id']) ? intval($_GET['set_anime_id']) : 0;
        if ($animeId === 0) {
            $animes = $adapter->getAllAnimes();
            echo '<br><h3>';
            echo '<p class="text-center">';
            foreach ($animes as $anime) {
                if (in_array($anime[UserEnum::COL_ID], $availAnime) || 
                        $this->_currentUser->getRole() === UserEnum::ROLE_MEGA_ADMIN ||
                        $this->_currentUser->getRole() === UserEnum::ROLE_ADMIN) {
                    echo '<a href="adminpanel.php?go=' . self::ROUTE_UPLOAD_EPISODE . '&set_anime_id=' . $anime['id'] . '">'
                        . $anime[UserEnum::COL_ID] . ' — ' . $anime[UserEnum::COL_NAME] . '</a><br>';
                }
            }
            echo '</p></h3>';
        }
        else {
            if (!in_array($animeId, $availAnime) && 
                    $this->_currentUser->getRole() !== UserEnum::ROLE_MEGA_ADMIN &&
                    $this->_currentUser->getRole() !== UserEnum::ROLE_ADMIN) {
                $this->_printError('You can\'t be here');
                return;
            }
            
            $anime = $adapter->getAnimeById($animeId);
            $episodeId = isset($_GET['set_id']) ? intval($_GET['set_id']) : 0;
            $episodeAdapter = $this->_currentUser->getAdapter()->getEpisodeAdapter();
            if (isset($_GET['new_episode'])) {
                if (isset($_POST['episode_name']) && isset($_POST['episode_number'])) {
                    $episode = array();
                    $episode[EpisodeDataAdapter::COL_NAME] = $_POST['episode_name'];
                    $episode[EpisodeDataAdapter::COL_NUMBER] = $_POST['episode_number'];
                    $episode[EpisodeDataAdapter::COL_SUB_VIDEO_ID] = $_POST['sub_video_id'];
                    $episode[EpisodeDataAdapter::COL_DUB_VIDEO_ID] = $_POST['dub_video_id'];
                    $episode[EpisodeDataAdapter::COL_SUB_PATH] = '';
                    $episode[EpisodeDataAdapter::COL_DUB_SUB_PATH] = '';
                    
                    $episodeId = $episodeAdapter->createNewEpisode($episode[EpisodeDataAdapter::COL_NAME], $episode[EpisodeDataAdapter::COL_NUMBER], $animeId,
                            $episode[EpisodeDataAdapter::COL_SUB_VIDEO_ID], $episode[EpisodeDataAdapter::COL_SUB_PATH],
                            $episode[EpisodeDataAdapter::COL_DUB_VIDEO_ID], $episode[EpisodeDataAdapter::COL_DUB_SUB_PATH]);
                    
                    $logArray = array(
                                Logger::IP => $this->_getCurrentUserIp(),
                                Logger::ACTION => Logger::ACTION_EPISODE_UPLOAD,
                                Logger::SUBJECT_ID => $episodeId,
                        );
                    
                    if ($episodeId) {
                        if (isset($_FILES['sub_file']) && $_FILES['sub_file']['error'] == UPLOAD_ERR_OK) {
                            $tmp_name = $_FILES['sub_file']['tmp_name'];
                            move_uploaded_file($tmp_name, './anime/sub_' . $episodeId . '.ass');
                            $episode[EpisodeDataAdapter::COL_SUB_PATH] = 'http://risensteam.ru/anime/sub_' . $episodeId . '.ass';
                        }
                        
                        if (isset($_FILES['dub_sub_file']) && $_FILES['dub_sub_file']['error'] == UPLOAD_ERR_OK) {
                            $tmp_name = $_FILES['dub_sub_file']['tmp_name'];
                            move_uploaded_file($tmp_name, './anime/dub_' . $episodeId . '.ass');
                            $episode[EpisodeDataAdapter::COL_DUB_SUB_PATH] = 'http://risensteam.ru/anime/dub_' . $episodeId . '.ass';
                        }
                        $episodeAdapter->updateEpisode($episodeId, $episode[EpisodeDataAdapter::COL_NAME], $episode[EpisodeDataAdapter::COL_NUMBER],
                            $episode[EpisodeDataAdapter::COL_SUB_VIDEO_ID], $episode[EpisodeDataAdapter::COL_SUB_PATH],
                            $episode[EpisodeDataAdapter::COL_DUB_VIDEO_ID], $episode[EpisodeDataAdapter::COL_DUB_SUB_PATH]);
                        
                        $logArray[Logger::STATUS] = Logger::STATUS_OK;
                        echo '<script>';
                        echo 'alert("Эпизод добавлен");';
                        echo 'window.location.replace("?go=' . self::ROUTE_UPLOAD_EPISODE . '&set_anime_id=' . $animeId . '&set_id=' . $episodeId . '");';
                        echo '</script>';
                    }
                    else {
                        $logArray[Logger::STATUS] = Logger::STATUS_FAIL;
                        $this->_printError('Could not create episode');
                    }
                    
                    $id = $this->_currentUser->getId();
                    Logger::getInstance()->log($id, $logArray);
                }
                else {
                    echo '<br><a href="?go=' . self::ROUTE_UPLOAD_EPISODE . '&set_anime_id=' . $animeId . '">Назад</a>';
                    echo '<form enctype="multipart/form-data" action="adminpanel.php?go=' . self::ROUTE_UPLOAD_EPISODE . '&set_anime_id=' . $animeId . '&new_episode" method="post">';
                    echo 'Название эпизода: <input type="text" required name="episode_name" id="name" value="Серия " /><br>';
                    echo 'Порядковый номер (для списка): <input type="text" required name="episode_number" id="number" /><hr>';
                    echo 'ID видео для субтитров: <input type="text" name="sub_video_id" id="sub_video_id" /><br>';
                    echo 'Субтитры: <input type="file" name="sub_file" id="sub_file" ><hr>';
                    echo 'ID видео для озвучки: <input type="text" name="dub_video_id" id="dub_video_id" /><br>';
                    echo 'Субтитры для озвучки: <input type="file" name="dub_sub_file" id="dub_sub_file" ><hr>';
                    echo '<input type="submit" value="Submit"><br>';
                    echo '</form>';
                }
            }
            elseif ($episodeId === 0) {
                echo '<br><a href="?go=' . self::ROUTE_UPLOAD_EPISODE . '">Назад</a>';
                $episodes = $episodeAdapter->getEpisodesByAnimeId($animeId);

                echo '<br><h3>';
                echo '<p class="text-center">';
                echo $anime[UserEnum::COL_NAME] . '<br>';
                if (count($episodes) >  6) {
                    echo '<a href="adminpanel.php?go=' . self::ROUTE_UPLOAD_EPISODE . '&set_anime_id=' . $animeId . '&new_episode">Новая серия</a><br>';
                }
                foreach ($episodes as $episode) {
                    echo '<a href="adminpanel.php?go=' . self::ROUTE_UPLOAD_EPISODE . '&set_anime_id=' . $animeId . '&set_id=' . $episode[UserEnum::COL_ID] . '">'
                        . $episode[UserEnum::COL_NAME] . ' — ' . $episode[UserEnum::COL_ID] . '</a><br>';
                }
                echo '<a href="adminpanel.php?go=' . self::ROUTE_UPLOAD_EPISODE . '&set_anime_id=' . $animeId . '&new_episode">Новая серия</a><br>';
                echo '</p></h3>';
            }
            else {
                echo '<br><a href="?go=' . self::ROUTE_UPLOAD_EPISODE . '&set_anime_id=' . $animeId . '">Назад</a>';
                $episode = $episodeAdapter->getEpisodeById($episodeId);
                if ($episode[EpisodeDataAdapter::COL_ANIME_ID] !== $animeId) {
                    $this->_printError('You can\'t be here');
                    return;
                }
                
                if (isset($_POST['episode_name']) && isset($_POST['episode_number'])) {
                    $contentBefore = $episode;
                    $episode[EpisodeDataAdapter::COL_NAME] = $_POST['episode_name'];
                    $episode[EpisodeDataAdapter::COL_NUMBER] = $_POST['episode_number'];
                    $episode[EpisodeDataAdapter::COL_SUB_VIDEO_ID] = $_POST['sub_video_id'];
                    $episode[EpisodeDataAdapter::COL_DUB_VIDEO_ID] = $_POST['dub_video_id'];
                    
                    if (isset($_FILES['sub_file']) && $_FILES['sub_file']['error'] == UPLOAD_ERR_OK) {
                        $tmp_name = $_FILES['sub_file']['tmp_name'];
                        move_uploaded_file($tmp_name, './anime/sub_' . $episodeId . '.ass');
                        $episode[EpisodeDataAdapter::COL_SUB_PATH] = 'http://risensteam.ru/anime/sub_' . $episodeId . '.ass';
                    }
                    if (isset($_FILES['dub_sub_file']) && $_FILES['dub_sub_file']['error'] == UPLOAD_ERR_OK) {
                        $tmp_name = $_FILES['dub_sub_file']['tmp_name'];
                        move_uploaded_file($tmp_name, './anime/dub_' . $episodeId . '.ass');
                        $episode[EpisodeDataAdapter::COL_DUB_SUB_PATH] = 'http://risensteam.ru/anime/dub_' . $episodeId . '.ass';
                    }
                    
                    $episodeAdapter->updateEpisode($episodeId, $episode[EpisodeDataAdapter::COL_NAME], $episode[EpisodeDataAdapter::COL_NUMBER],
                            $episode[EpisodeDataAdapter::COL_SUB_VIDEO_ID], $episode[EpisodeDataAdapter::COL_SUB_PATH],
                            $episode[EpisodeDataAdapter::COL_DUB_VIDEO_ID], $episode[EpisodeDataAdapter::COL_DUB_SUB_PATH]);
                    
                    echo '<script>';
                    echo 'alert("Эпизод обновлен");';
                    echo '</script>';
                    
                    $logArray = array(
                                Logger::IP => $this->_getCurrentUserIp(),
                                Logger::ACTION => Logger::ACTION_EPISODE_UPDATE,
                                Logger::STATUS => Logger::STATUS_OK,
                                Logger::SUBJECT_ID => $episodeId,
                                Logger::CONTENT_BEFORE => $contentBefore,
                                Logger::CONTENT_AFTER => $episode,
                        );
                    $id = $this->_currentUser->getId();
                    Logger::getInstance()->log($id, $logArray);
                }
                elseif (isset($_GET['delete'])) {
                    $logArray = array(
                                Logger::IP => $this->_getCurrentUserIp(),
                                Logger::ACTION => Logger::ACTION_EPISODE_DELETE,
                                Logger::SUBJECT_ID => $episodeId,
                        );
                    
                    if ($episodeAdapter->removeEpisode($episodeId)) {
                        $logArray[Logger::STATUS] = Logger::STATUS_OK;
                        echo '<script>';
                        echo 'alert("Эпизод удален");';
                        echo 'window.location.replace("?go=' . self::ROUTE_UPLOAD_EPISODE . '&set_anime_id=' . $animeId . '");';
                        echo '</script>';
                    }
                    else {
                        $logArray[Logger::STATUS] = Logger::STATUS_FAIL;
                        $this->_printError('Не удалось удалить');
                    }
                    
                    $id = $this->_currentUser->getId();
                    Logger::getInstance()->log($id, $logArray);
                }
                
                echo '<form enctype="multipart/form-data" action="adminpanel.php?go=' . self::ROUTE_UPLOAD_EPISODE . '&set_anime_id=' . $animeId . '&set_id=' . $episodeId . '" method="post">';
                echo 'Название эпизода: <input type="text" required name="episode_name" id="name" value="' . $episode[EpisodeDataAdapter::COL_NAME] . '" /><br>';
                echo 'Порядковый номер (для списка): <input type="text" required name="episode_number" id="number" value="' . $episode[EpisodeDataAdapter::COL_NUMBER] . '" /><hr>';
                echo 'ID видео для субтитров: <input type="text" name="sub_video_id" id="sub_video_id" value="' . $episode[EpisodeDataAdapter::COL_SUB_VIDEO_ID] . '" /><br>';
                echo 'Субтитры: <input type="file" name="sub_file" id="sub_file" ><hr>';
                echo 'ID видео для озвучки: <input type="text" name="dub_video_id" id="dub_video_id" value="' . $episode[EpisodeDataAdapter::COL_DUB_VIDEO_ID] . '" /><br>';
                echo 'Субтитры для озвучки: <input type="file" name="dub_sub_file" id="dub_sub_file" ><hr>';
                echo '<input type="submit" value="Submit"><hr>';
                echo '<a href="adminpanel.php?go=' . self::ROUTE_UPLOAD_EPISODE . '&set_anime_id=' . $animeId . '&set_id=' . $episodeId . '&delete">Удалить</a>';
                echo '</form>';
            }
        }
    }
    
    protected function _printUploadChapterMenu() {
        
        $adapter = $this->_currentUser->getAdapter()->getMangaAdapter();
        $yandexAdapter = $this->_currentUser->getAdapter()->getYandexDiskAdapter();
        $availManga = $this->_currentUser->getAvailManga();
        
        $mangaId = isset($_GET['set_manga_id']) ? intval($_GET['set_manga_id']) : 0;
        if ($mangaId === 0) {
            $mangas = $adapter->getAllMangas();
            echo '<br><h3>';
            echo '<p class="text-center">';
            foreach ($mangas as $manga) {
                if (in_array($manga[UserEnum::COL_ID], $availManga) || 
                        $this->_currentUser->getRole() === UserEnum::ROLE_MEGA_ADMIN ||
                        $this->_currentUser->getRole() === UserEnum::ROLE_ADMIN) {
                    echo '<a href="adminpanel.php?go=' . self::ROUTE_UPLOAD_CHAPTER . '&set_manga_id=' . $manga['id'] . '">'
                        . $manga[UserEnum::COL_ID] . ' — ' . $manga[UserEnum::COL_NAME] . '</a><br>';
                }
            }
            echo '</p></h3>';
        }
        else {
            if (!in_array($mangaId, $availManga) && 
                    $this->_currentUser->getRole() !== UserEnum::ROLE_MEGA_ADMIN &&
                    $this->_currentUser->getRole() !== UserEnum::ROLE_ADMIN) {
                $this->_printError('You can\'t be here');
                return;
            }
            
            $manga = $adapter->getMangaById($mangaId);
            $chapterId = isset($_GET['set_id']) ? intval($_GET['set_id']) : 0;
            $chapterAdapter = $this->_currentUser->getAdapter()->getChapterAdapter();
            if (isset($_GET['new_chapter'])) {
                if (isset($_POST['name']) && isset($_POST['number'])) {
                    $chapter = array();
                    $chapter[ChapterDataAdapter::COL_NAME] = $_POST['name'];
                    $chapter[ChapterDataAdapter::COL_NUMBER] = $_POST['number'];
                    $chapter[ChapterDataAdapter::COL_CHAPTER_NAME] = $_POST['chapter_name'];
                    $chapter[ChapterDataAdapter::COL_DOWNLOAD] = '';
                    $chapter[ChapterDataAdapter::COL_LINKS] = '';
                    
                    $chapterId = $chapterAdapter->createNewChapter($chapter[ChapterDataAdapter::COL_NAME], $chapter[ChapterDataAdapter::COL_NUMBER], $mangaId,
                            $chapter[ChapterDataAdapter::COL_CHAPTER_NAME], $chapter[ChapterDataAdapter::COL_LINKS],
                            $chapter[ChapterDataAdapter::COL_DOWNLOAD]);
                    
                    if (isset($_FILES['chapter_files'])) {
                        $yandexAdapter->createFolder(YandexDiskAdapter::MANGA_FOLDER . '/' . $chapterId);
                        
                        foreach ($_FILES["chapter_files"]["error"] as $key => $error) {
                            if ($error == UPLOAD_ERR_OK) {
                                $tmp_name = $_FILES["chapter_files"]["tmp_name"][$key];
                                $namep = basename($_FILES["chapter_files"]["name"][$key]);
                                
                                $yandexAdapter->uploadFile($chapterId, $namep, $tmp_name);
                            }
                        }
                    }
                    
                    $logArray = array(
                                Logger::IP => $this->_getCurrentUserIp(),
                                Logger::ACTION => Logger::ACTION_CHAPTER_UPLOAD,
                                Logger::SUBJECT_ID => $chapterId,
                        );
                    
                    if ($chapterId) {
                        $yandexAdapter->clearChapterCache($chapterId);
                        $logArray[Logger::STATUS] = Logger::STATUS_OK;
                        echo '<script>';
                        echo 'alert("Глава добавлена");';
                        echo 'window.location.replace("?go=' . self::ROUTE_UPLOAD_CHAPTER . '&set_manga_id=' . $mangaId . '&set_id=' . $chapterId . '");';
                        echo '</script>';
                    }
                    else {
                        $logArray[Logger::STATUS] = Logger::STATUS_FAIL;
                        $this->_printError('Could not create chapter');
                    }
                    
                    $id = $this->_currentUser->getId();
                    Logger::getInstance()->log($id, $logArray);
                }
                else {
                    echo '<br><a href="?go=' . self::ROUTE_UPLOAD_CHAPTER . '&set_manga_id=' . $mangaId . '">Назад</a>';
                    echo '<form enctype="multipart/form-data" action="adminpanel.php?go=' . self::ROUTE_UPLOAD_CHAPTER . '&set_manga_id=' . $mangaId . '&new_chapter" method="post">';
                    echo 'Название главы с номером: <input type="text" required name="name" id="name" value="Глава " /><br>';
                    echo 'Порядковый номер главы (для списка): <input type="text" required name="number" id="number" /><br>';
                    echo 'Название главы: <input type="text" name="chapter_name" id="chapter_name" /><hr>';
                    echo '<input type="file" name="chapter_files[]" id="chapter_files" multiple><br>';
                    echo '<input type="submit" value="Submit"><hr>';
                    echo '</form>';
                }
            }
            elseif ($chapterId === 0) {
                echo '<br><a href="?go=' . self::ROUTE_UPLOAD_CHAPTER . '">Назад</a>';
                $chapters = $chapterAdapter->getChaptersByMangaId($mangaId);

                echo '<br><h3>';
                echo '<p class="text-center">';
                echo $manga[UserEnum::COL_NAME] . '<br>';
                if (count($chapters) >  6) {
                    echo '<a href="adminpanel.php?go=' . self::ROUTE_UPLOAD_CHAPTER . '&set_manga_id=' . $mangaId . '&new_chapter">Новая глава</a><br>';
                }
                foreach ($chapters as $chapter) {
                    echo '<a href="adminpanel.php?go=' . self::ROUTE_UPLOAD_CHAPTER . '&set_manga_id=' . $mangaId . '&set_id=' . $chapter[UserEnum::COL_ID] . '">'
                        . $chapter[UserEnum::COL_NAME] . ' — ' . $chapter[UserEnum::COL_ID] . '</a><br>';
                }
                echo '<a href="adminpanel.php?go=' . self::ROUTE_UPLOAD_CHAPTER . '&set_manga_id=' . $mangaId . '&new_chapter">Новая глава</a><br>';
                echo '</p></h3>';
            }
            else {
                echo '<br><a href="?go=' . self::ROUTE_UPLOAD_CHAPTER . '&set_manga_id=' . $mangaId . '">Назад</a>';
                $chapter = $chapterAdapter->getChapterById($chapterId);
                if ($chapter[ChapterDataAdapter::COL_MANGA_ID] !== $mangaId) {
                    $this->_printError('You can\'t be here');
                    return;
                }
                
                if (isset($_POST['chapter_name']) && isset($_POST['number'])) {
                    $contentBefore = $chapter;
                    $chapter[ChapterDataAdapter::COL_NAME] = $_POST['name'];
                    $chapter[ChapterDataAdapter::COL_NUMBER] = $_POST['number'];
                    $chapter[ChapterDataAdapter::COL_CHAPTER_NAME] = $_POST['chapter_name'];
                    $chapter[ChapterDataAdapter::COL_DOWNLOAD] = $_POST['download'];
                    
                    if (isset($_FILES['chapter_files'])) {
                        $yandexAdapter->createFolder(YandexDiskAdapter::MANGA_FOLDER . '/' . $chapterId);
                        
                        foreach ($_FILES["chapter_files"]["error"] as $key => $error) {
                            if ($error == UPLOAD_ERR_OK) {
                                $tmp_name = $_FILES["chapter_files"]["tmp_name"][$key];
                                $namep = basename($_FILES["chapter_files"]["name"][$key]);
                                
                                $yandexAdapter->uploadFile($chapterId, $namep, $tmp_name);
                            }
                        }
                    }
                    
                    $chapterAdapter->updateChapter($chapterId, $chapter[ChapterDataAdapter::COL_NAME], $chapter[ChapterDataAdapter::COL_NUMBER],
                            $chapter[ChapterDataAdapter::COL_CHAPTER_NAME], $chapter[ChapterDataAdapter::COL_LINKS],
                            $chapter[ChapterDataAdapter::COL_DOWNLOAD]);
                    
                    $yandexAdapter->clearChapterCache($chapterId);
                    echo '<script>';
                    echo 'alert("Глава обновлена");';
                    echo '</script>';
                    
                    $logArray = array(
                                Logger::IP => $this->_getCurrentUserIp(),
                                Logger::ACTION => Logger::ACTION_CHAPTER_UPDATE,
                                Logger::STATUS => Logger::STATUS_OK,
                                Logger::SUBJECT_ID => $chapterId,
                                Logger::CONTENT_BEFORE => $contentBefore,
                                Logger::CONTENT_AFTER => $chapter,
                        );
                    $id = $this->_currentUser->getId();
                    Logger::getInstance()->log($id, $logArray);
                }
                elseif (isset($_GET['delete'])) {
                    $logArray = array(
                                Logger::IP => $this->_getCurrentUserIp(),
                                Logger::ACTION => Logger::ACTION_CHAPTER_DELETE,
                                Logger::SUBJECT_ID => $chapterId,
                        );
                    
                    if ($chapterAdapter->removeChapter($chapterId)) {
                        $logArray[Logger::STATUS] = Logger::STATUS_OK;
                        $yandexAdapter->clearFolder($chapterId);
                        echo '<script>';
                        echo 'alert("Глава удалена");';
                        echo 'window.location.replace("?go=' . self::ROUTE_UPLOAD_CHAPTER . '&set_manga_id=' . $mangaId . '");';
                        echo '</script>';
                    }
                    else {
                        $logArray[Logger::STATUS] = Logger::STATUS_FAIL;
                        $this->_printError('Не удалось удалить');
                    }
                    
                    $id = $this->_currentUser->getId();
                    Logger::getInstance()->log($id, $logArray);
                }
                elseif (isset($_GET['clear'])) {
                    $logArray = array(
                                Logger::IP => $this->_getCurrentUserIp(),
                                Logger::ACTION => Logger::ACTION_CHAPTER_CLEAR,
                                Logger::SUBJECT_ID => $chapterId,
                        );
                    
                    if ($yandexAdapter->clearFolder($chapterId)) {
                        $logArray[Logger::STATUS] = Logger::STATUS_OK;
                        echo '<script>';
                        echo 'alert("Глава очишена");';
                        echo 'window.location.replace("?go=' . self::ROUTE_UPLOAD_CHAPTER . '&set_manga_id=' . $mangaId . '&set_id=' . $chapterId . '");';
                        echo '</script>';
                    }
                    else {
                        $logArray[Logger::STATUS] = Logger::STATUS_FAIL;
                        $this->_printError('Не удалось очистить');
                    }
                    
                    $id = $this->_currentUser->getId();
                    Logger::getInstance()->log($id, $logArray);
                }
                
                echo '<form enctype="multipart/form-data" action="adminpanel.php?go=' . self::ROUTE_UPLOAD_CHAPTER . '&set_manga_id=' . $mangaId . '&set_id=' . $chapterId . '" method="post">';
                echo 'Название главы с номером: <input type="text" required name="name" id="name" value="' . $chapter[ChapterDataAdapter::COL_NAME] . '" /><br>';
                echo 'Порядковый номер главы (для списка): <input type="text" required name="number" id="number" value="' . $chapter[ChapterDataAdapter::COL_NUMBER] . '" /><br>';
                echo 'Название главы: <input type="text" name="chapter_name" id="chapter_name" value="' . $chapter[ChapterDataAdapter::COL_CHAPTER_NAME] . '" /><hr>';
                echo '<input type="file" name="chapter_files[]" id="chapter_files" multiple><br>';
                echo '<a href="adminpanel.php?go=' . self::ROUTE_UPLOAD_CHAPTER . '&set_manga_id=' . $mangaId . '&set_id=' . $chapterId . '&clear">Очистить папку</a><hr>';
                echo '<input type="submit" value="Submit"><hr>';
                echo '<a href="adminpanel.php?go=' . self::ROUTE_UPLOAD_CHAPTER . '&set_manga_id=' . $mangaId . '&set_id=' . $chapterId . '&delete">Удалить</a>';
                echo '</form>';
            }
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
    
    protected function _getCurrentUserIp() {
        return $this->_currentUser->getIp();
    }
    
    protected function _showLogs() {
        $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
        $logs = Logger::getInstance()->getPage($page);
        echo '<hr><table width="100%">';
        echo '<tr><th>ID действия</th><th>Пользователь</th><th>Время</th><td width="50%">Контент</td></tr>';
        
        foreach ($logs as $log) {
            echo '<tr>';
            
            $userName = Logger::getInstance()->getUserNameById($log[LoggerDataAdapter::COL_USER]);
            
            echo '<td>' . $log[LoggerDataAdapter::COL_ID] . '</td>';
            echo '<td>' . $userName . ' (' . $log[LoggerDataAdapter::COL_USER] . ')</td>';
            echo '<td>' . $log[LoggerDataAdapter::COL_DATE] . '</td>';
            
            $values = json_decode($log[LoggerDataAdapter::COL_VALUE]);
            
            echo '<td><table width="10%" border="3px"><tr>';
            foreach ($values as $key => $val) {
                echo '<th>' . $key . '</th>';
            }
            echo '</tr><tr>';
            foreach ($values as $key => $val) {
                if ($key !== Logger::CONTENT_BEFORE && $key !== Logger::CONTENT_AFTER) {
                    echo '<td>' . $val . '</td>';
                }
                else {
                    echo '<td><table width="30%" border="1px"><tr>';
                    
                    foreach ($val as $contentKey => $content) {
                        echo '<td>' . $contentKey . '</td>';
                    }
                    echo '</tr><tr>';
                    
                    foreach ($val as $content) {
                        echo '<td>' . $content . '</td>';
                    }
                    
                    echo '</tr></table></td>';
                }
            }
            echo '</tr></table></td>';
            
            echo '</tr>';
        }
        
        echo '</table><hr>| ';
        $pageCount = Logger::getInstance()->getPageCount();
        for ($i = 0; $i < $pageCount; $i++) {
            if ($i !== $page) {
                echo '<a href="adminpanel.php?go=' . self::ROUTE_SHOW_LOGS . '&page=' . $i . '">' . ($i + 1) . '</a> | ';
            }
            else {
                echo ($i + 1) . ' | ';
            }
        }
    }
}