<?php
$id = intval($_GET['id']);
if ($id === 0) die();

include_once '../bd.php';
include_once '../Admin/AnimeCacheDataAdapter.php';

$returnArray = array('error' => '');

$adapter = new AnimeCacheDataAdapter($mysqli);
$mp4Link = $adapter->getAnimeFromCache($id);

if (!$mp4Link) {
  $url = 'http://video.sibnet.ru/video' . $id;
  $videoPage = file_get_contents($url);
  $pos1 = strpos($videoPage, '/v/');
  $pos2 = strpos($videoPage, '/v/', $pos1 + 3);
  $pos3 = strpos($videoPage, '.m3u8', $pos2);
  $newLink = 'http://video.sibnet.ru' . substr($videoPage, $pos2, $pos3 - $pos2) . '.m3u8';

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $newLink);
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $a = curl_exec($ch); // $a will contain all headers

  $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // This is what you need, it will return you the last effective URL
  curl_close($ch);

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  $data = curl_exec($ch);
  curl_close($ch);

  $pos1 = strpos($data, 'http');
  $pos2 = strpos($data, '&noip=1');

  $mp4Link = substr($data, $pos1, $pos2 - $pos1) . '&noip=1';
  $mp4Link = str_replace('.ts', '.mp4', $mp4Link);
  if ($mp4Link != '&noip=1') {
    $pos1 = strpos($mp4Link, '&e=');
    $pos2 = strpos($mp4Link, '&', $pos1 + 3);
    $expires = substr($mp4Link, $pos1 + 3, $pos2 - $pos1 - 3);
    $adapter->addAnimeCache($id, $expires, $mp4Link);
    $returnArray['cached'] = 'false';
    $returnArray['expires'] = $expires;
  }
}
else {
  $returnArray['cached'] = 'true';
  $returnArray['expires'] = $adapter->getLastCacheExpire();
}
$returnArray['url'] = $mp4Link;

if ($mp4Link == '&noip=1') {
  $returnArray['error'] = 'Wrong ID or service is unavailable';
}

echo json_encode($returnArray);
