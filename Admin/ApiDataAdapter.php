<?php
include_once('DataAdapter.php');
class ApiDataAdapter extends DataAdapter {

    public function registerKey() {
        //Создаем ключ из id пользователя, времени и соли
        $uid = intval($_POST['user_id']);
        $key = hash('sha512', $uid . time() . SALT);
        $defaultKeyState = 1; //1 - active, 0 - inactive

        $sql = $this->_mysqli->prepare('INSERT INTO `api_keys`(`id`, `user_id`, `date`, `api_key`, `active`) VALUES (NULL ,?, ?, ?, ?)');
        $sql->bind_param('issi', $uid, date("Y-m-d H:i:s", time()), $key, $defaultKeyState);
        $done = $sql->execute();

        if($done) {
            echo '<p class=\"text-center\">' . $key . '</p>';
            return $sql->insert_id;
        } else {
            echo '<p class=\"text-center\">API ключ для этого пользователя уже существует!</p>'; //Всё волшебство работает так, что на одного пользователя один ключ
            return false;  //Поэтому при создании таблицы поле user_id я сделал уникальным. Если так не надо, то это сообщение можно либо убрать, либо оставить для ошибок
        }
    }

    public function getUserKeys() {
        //Тут всё просто, берутся все записи и возвращаются массивом, который потом разбираем
        $sql = $this->_mysqli->prepare('SELECT * FROM api_keys a INNER JOIN users u ON a.user_id = u.id');
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
    
    public function getKey() {
        $uid = intval($_POST['user_id']);
       
        $sql = $this->_mysqli->prepare('SELECT * from api_keys WHERE user_id=?');
        $sql->bind_param('i', $uid);
        
        $status = $sql->execute();
        if ($status) {
            $result = $sql->get_result();
            $returnArray = array();

            $row = $result->fetch_assoc();
            return $row;
        }
    }
    
    public function updateData() {
        //Создаем ключ, если надо переписать и переменные для $_POST значений
        $uid = intval($_POST['user_id']);
        $newKey = hash('sha512', $uid . time() . SALT);
        $status = boolval($_POST['status']);
        $id = intval($_POST['id']);

        //Делаем два разных запроса, на случай с заменой ключа и на случай без замены, потому что как по другому - я не знаю
        $queryWithKey = 'UPDATE `api_keys` SET `user_id`=?, `api_key`=?, `active`=' . $status . ' WHERE `id`=' . $id;
        $queryWithoutKey = 'UPDATE `api_keys` SET `user_id`=?, `active`=' . $status . ' WHERE `id`=' . $id;

        //Проверяем, если повторная генерация отмечена, то берём запрос $queryWithKey, иначе $queryWithoutKey
        if(isset($_POST['new_key'])){
            $sql = $this->_mysqli->prepare($queryWithKey);
            $sql->bind_param('is', $uid, $newKey);
        } else {
            $sql = $this->_mysqli->prepare($queryWithoutKey);
            $sql->bind_param('i', $uid);
        }
        $result = $sql->execute();

        if($result) {
            echo "<p class=\"text-center\">Data updated!</p>";
            return $id;
        } else {
            return false;
        }
    }

    //Метод проверки ключа
    public function checkAPIKey($key) {
        //Делаем запрос к базе для проверки существования и активности переданного в функцию ключа
        $sql = $this->_mysqli->prepare('SELECT * FROM api_keys WHERE api_key=? AND active=1');
        $sql->bind_param('s', $key);
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
}