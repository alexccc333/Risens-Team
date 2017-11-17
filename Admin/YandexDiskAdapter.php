<?php

class YandexDiskAdapter {
    const GET_FILES_IN_FOLDER_URL = 'https://cloud-api.yandex.net:443/v1/disk/resources?path=';
    const GET_DONWLOAD_LINK_FOR_FILE_URL = 'https://cloud-api.yandex.net:443/v1/disk/resources/download?path=';
    const CREATE_FOLDER_URL = 'https://cloud-api.yandex.net:443/v1/disk/resources?path=';
    const UPLOAD_FILE_URL = 'https://cloud-api.yandex.net:443/v1/disk/resources/upload?overwrite=true&path=';
    const CHECK_URL = 'https://cloud-api.yandex.net:443/v1/disk/resources?path=';
    const DELETE_URL = 'https://cloud-api.yandex.net:443/v1/disk/resources?path=';
    
    const MANGA_FOLDER = 'Manga';
    
    protected $_adapter;
    
    public function __construct($mysqli) {
        $this->_adapter = new MangaCacheDataAdapter($mysqli);
    }
    
    protected function _getRequestContext() {
        $opts = array(
            'http'=>array(
                'header' => 'Authorization: OAuth ' . YANDEX_TOKEN,
            )
        );
        return stream_context_create($opts);
    }
    
    public function getManga($chapterId) {
        $path = $this->_convertPath(self::MANGA_FOLDER . '/' . $chapterId);
        $cache = $this->_checkCache($chapterId);
        
        if (!$cache) {
            $files = $this->_getFilesInFolder($path);
            $link = $this->_getDonwloadLink($path);
            $this->_addToCache($files, $link, $chapterId);
            return array($files, $link);
        }
        else {
            return array($cache[MangaCacheDataAdapter::COL_PAGES], $cache[MangaCacheDataAdapter::COL_LINK]);
        }
    }
    
    protected function _checkCache($chapterId) {
        return $this->_adapter->getMangaFromCache($chapterId);
    }
    
    protected function _addToCache($pages, $link, $chapterId) {
        $this->_adapter->addMangaCache($chapterId, $pages, $link);
    }

    protected function _getFilesInFolder($path) {
        $this->createFolder($path);
        
        $path = $this->_convertPath($path);
        $url = self::GET_FILES_IN_FOLDER_URL . $path;
        $context = $this->_getRequestContext();
        
        $response = file_get_contents($url, false, $context);
        $pages = $this->_parseFilesInFolder($response);
        
        return $this->_getDownloadLinks($pages);
    }
    
    protected function _getDonwloadLink($path) {
        $path = $this->_convertPath($path);
        $url = self::GET_DONWLOAD_LINK_FOR_FILE_URL . $path;
        $context = $this->_getRequestContext();
        
        $response = file_get_contents($url, false, $context);
        
        return $this->_parseDownloadLink($response);
    }
    
    protected function _parseFilesInFolder($response) {
        $decodedResponse = json_decode($response);
        $items = $decodedResponse->_embedded->items;
        $returnArray = array();
        
        foreach ($items as $item) {
            $returnArray[] = $item->path;
        }
        
        return $returnArray;
    }
    
    protected function _getDownloadLinks($array) {
        $returnString = '[';
        
        foreach($array as $page) {
            $page = $this->_convertPath($page);
            
            $url = self::GET_DONWLOAD_LINK_FOR_FILE_URL . $page;
            $context = $this->_getRequestContext();

            $response = file_get_contents($url, false, $context);
            $returnString .= '\'' . $this->_parseDownloadLink($response) . '\',';
        }
        
        $returnString .= '];';
        return $returnString;
    }
    
    protected function _parseDownloadLink($response) {
        $decodedResponse = json_decode($response);
        
        return $decodedResponse->href;
    }
    
    protected function _checkPath($path) {
        return true;
        $path = $this->_convertPath($path);
        $url = self::CHECK_URL . $path;
        $header = array('Authorization: OAuth ' . YANDEX_TOKEN);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        return $info['http_code'] == '201';
    }
    
    public function clearFolder($chapterId) {
        $path = $this->_convertPath(self::MANGA_FOLDER . '/' . $chapterId);
        
        if ($this->_checkPath($path)) {
            $url = self::DELETE_URL . $path;
            $header = array('Authorization: OAuth ' . YANDEX_TOKEN);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            
            $this->_adapter->removeMangaFromCacheByChapterId($chapterId);
            return true;
        }
    }
    
    public function createFolder($path) {
        if ($this->_checkPath($path)) {
            return;
        }
        
        $path = $this->_convertPath($path);
        $url = self::CREATE_FOLDER_URL . $path;
        $header = array('Authorization: OAuth ' . YANDEX_TOKEN);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($info['http_code'] != '201') {
            echo 'Error';
        }
    }
    
    protected function _getUploadLink($path) {
        $path = $this->_convertPath($path);
        $url = self::UPLOAD_FILE_URL . $path;
        $context = $this->_getRequestContext();
        
        $response = file_get_contents($url, false, $context);
        return $this->_parseDownloadLink($response);
    }
    
    public function uploadFile($chapterId, $file, $tempName) {
        $uploadUrl = $this->_getUploadLink(self::MANGA_FOLDER . '/' . $chapterId . '/' . $file);
        $header = array('Authorization: OAuth ' . YANDEX_TOKEN);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uploadUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($tempName));
        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($info['http_code'] != '201') {
            echo 'Error';
        }
    }

    protected function _convertPath($path) {
        $path = str_replace(' ', '%20', $path);
        return str_replace('/', '%2F', $path);
    }
}
