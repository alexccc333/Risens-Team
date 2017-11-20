<?php
include('API.php');
include('../bd.php');
$api = new API($mysqli);

if(preg_match("^/api/(anime|manga)/[0-9]+^", $api->getURI())){
    $id = explode('/', $api->getURI());
    $id = array_pop($id);
    echo $api->getRequest($id);
}
