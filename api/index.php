<?php
include('API.php');
include('../bd.php');
$api = new API($mysqli);

if(preg_match("^/api/(anime|manga)/([0-9]+)\?key\=([a-zA-Z0-9]+)^", $api->getURI())){

    $link = explode('/', $api->getURI());  //Целиковая ссылка поступает в $link и делится на части
    $tail = array_pop($link); //Берётся хвост, содержащий $id для получения нужной записи и api ключ $key
    $parts = explode('?key=', $tail); // делим хвост
    $id = array_shift($parts);  //на $id
    $key = array_pop($parts);  // и $key соответственно

    if($api->checkAPIKey($key)) { //проверяем, если ключ рабочий - выводится информация, а если нет - сообщение (может и не надо)
        echo $api->getRequest($id);
    } else {
        echo "API Key's invalid, inactive or does not exist!";
    }
}
