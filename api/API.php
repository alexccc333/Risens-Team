<?php

class API {

    protected $_mysqli = null;
    const TABLE_NAME_URI = 2;

    public function __construct($mysqli){
        $this->_mysqli = $mysqli;
    }

    //Смотрим на URI и получаем его для нужд Высшего Блага
    public function getURI(){
        $uri = $_SERVER['REQUEST_URI'];
        return $uri;
    }

    //Метод для вызова
    public function getRequest($id){
        $uri = $this->getURI();  //Смотрим на uri
        $table = explode('/', $uri); //делим на куски
        $table = $table[self::TABLE_NAME_URI]; // берём тот, который укажет на таблицу

        //Определяем таблицу для запроса
        switch($table){
            case 'anime':
                $data = array('id' , 'name', 'poster');
                $data = implode(',', $data);
                break;
            case 'manga':
                $data = array('id', 'name');
                $data = implode(',', $data);
        }

        //Формируем запрос и отправляем его
        $sql = $this->_mysqli->prepare("SELECT " . $data . " FROM ". $table. " WHERE id =" . $id);  //формируем запрос
        if ($id == '0'){
            $sql = $this->_mysqli->prepare("SELECT ". $data . " FROM " . $table . " WHERE 1");
        }
        $status= $sql->execute(); // делаем запрос
        if ($status) {
            $result = $sql->get_result();
            $returnArray = array();

            $row = $result->fetch_assoc();
            while ($row) {
                $returnArray[] = $row;
                $row = $result->fetch_assoc();
            }
            $result->free();

            return json_encode($returnArray);
        }
    }
}