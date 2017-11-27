<?php

class ApiDataAdapter extends DataAdapter {

    public function registerKey() {

        //Создаем ключ из id пользователя, времени и соли
        $key = hash('sha512', $_POST['user_id'] . time() . SALT);

        $sql = $this->_mysqli->prepare('INSERT INTO `api_keys`(`id`, `user_id`, `date`, `api_key`, `active`) VALUES (NULL ,?, ?, ?, 0)');
        $sql->bind_param('iss', $_POST['user_id'], date("Y-m-d H:i:s", time()), $key);
        $done = $sql->execute();

        if($done) {
            echo "<p class=\"text-center\">$key</p>";
            return $key;
        } else {
            echo "<p class=\"text-center\">API ключ для этого пользователя уже существует!</p>"; //Всё волшебство работает так, что на одного пользователя один ключ
            return false;  //Поэтому при создании таблицы поле user_id я сделал уникальным. Если так не надо, то это сообщение можно либо убрать, либо оставить для ошибок
        }

    }

    public function getUserKeys() {

        //Тут всё просто, берутся все записи и возвращаются массивом, который потом разбираем
        $sql = $this->_mysqli->prepare('SELECT * from api_keys WHERE 1');
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

    public function updateData() {

        //Создаем ключ, если надо переписать
        $new_key = hash('sha512', $_POST['user_id'] . time() . SALT);

        //Делаем два разных запроса, на случай с заменой ключа и на случай без замены, потому что как по другому - я не знаю
        $queryWithKey = 'UPDATE `api_keys` SET `user_id`=?, `api_key`=?, `active`=' .$_POST['status'] . ' WHERE `id`=' . $_POST['id'];
        $queryWithoutKey = 'UPDATE `api_keys` SET `user_id`=?, `active`=' . $_POST['status'] . ' WHERE `id`=' . $_POST['id'];

        //Проверяем, если повторная генерация отмечена, то берём запрос $queryWithKey, иначе $queryWithoutKey
        if(isset($_POST['new_key'])){
            $sql = $this->_mysqli->prepare($queryWithKey);
            $sql->bind_param('is', $_POST['user_id'], $new_key);
        } else {
            $sql = $this->_mysqli->prepare($queryWithoutKey);
            $sql->bind_param('i', $_POST['user_id']);
        }
        $result = $sql->execute();

        if($result) {
            echo "<p class=\"text-center\">Data updated!</p>";
            return true;
        } else {
            return false;
        }
    }

}