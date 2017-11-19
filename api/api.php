<?php

class api {

    protected $mysqli = null;

    //Смотрим на URI и получаем его для нужд Высшего Блага
    public function getURI() {
        $uri = $_SERVER['REQUEST_URI'];
        return $uri;
    }

    //Метод для вызова по id
    public function getRequest($id){
        $uri = $this->getURI();  //Смотрим на uri
        $table = explode('/', $uri); //делим на куски
        $table = $table[2]; // берём тот, который укажет на таблицу
        include('../bd.php'); //подрубаемся к бд
        $this->_mysqli = $mysqli;
        if($table == 'anime') { // если $table с анимой
            $data = ' id, name, poster '; //берём вот эти поля
        } else { // иначе смотрим мангу (ну, пока нужды для других нет)
            $data = ' id, name '; //вот эти столбцы
        }
        $sql = "SELECT".$data."FROM ".$table." WHERE id =".$id;  //формируем запрос
        $result = $mysqli->query($sql); // делаем запрос
        foreach ($result as $answer) { //берем результат
            return json_encode($answer);  //кодируем и отправляем
        }

    }

    //Метод для вызова всех элементов; тут всё то же самое
    public function getAllRequests() {
        include('../bd.php');
        $uri = $this->getURI();
        $table = explode('/', $uri);
        $table = $table[2];
        $this->_mysqli = $mysqli;
        if($table == 'anime') {
            $data = ' id, name, poster ';
        } else {
            $data = ' id, name ';
        }
        $sql = "SELECT".$data."FROM ".$table;
        $result = $mysqli->query($sql);
        foreach($result as $answer){
            $ok[] = $answer; //каждое значение пихаем в массив
        }
        return json_encode($ok); //возвращаем
    }

}