<?php
include('api.php');
$api = new api;

//Смотрим
if(preg_match("^/api/(anime|manga)/[0-9]+^", $api->getURI())){
    $id = explode('/', $api->getURI());
    $id = $id[3];
    echo $api->getRequest($id);

} elseif(preg_match("^/api/(anime|manga)^", $api->getURI())) {
    echo $api->getAllRequests();
}
