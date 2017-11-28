<?php
include('API.php');
include('../bd.php');
include_once('../Admin/ApiDataAdapter.php');
$api = new API($mysqli);
$adapter = new ApiDataAdapter($mysqli);

if(preg_match("^/api/(anime|manga)/([0-9]+)\?key\=([a-zA-Z0-9]+)^", $api->getURI())){

    $link = explode('/', $api->getURI());  //Целиковая ссылка поступает в $link и делится на части
    $tail = array_pop($link); //Берётся хвост, содержащий $id для получения нужной записи и api ключ $key
    $parts = explode('?key=', $tail); // Отделяем $id от ключа
    $id = array_shift($parts);
    $key = $_GET['key'];

    if($adapter->checkAPIKey($key)) { //проверяем, если ключ рабочий - выводится информация, а если нет - сообщение (может и не надо)
        echo $api->getRequest($id);
    } else {
        echo "API Key's invalid, inactive or does not exist!";
    }
}
