<?php
include 'Admin/MangaCacheDataAdapter.php';

class YandexDiskAdapter {
    const GET_FILES_IN_FOLDER_URL = 'https://cloud-api.yandex.net:443/v1/disk/resources?path=';
    const GET_DONWLOAD_LINK_FOR_FILE_URL = 'https://cloud-api.yandex.net:443/v1/disk/resources/download?path=';
    
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
    
    public function getManga($path, $chapterId) {
        $path = $this->_convertPath($path);
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
    
    protected function _convertPath($path) {
        $path = str_replace(' ', '%20', $path);
        return str_replace('/', '%2F', $path);
    }
}
