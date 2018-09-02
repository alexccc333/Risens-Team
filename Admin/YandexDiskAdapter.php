<?php
require_once 'MangaCacheDataAdapter.php';

class YandexDiskAdapter {
    const GET_FILES_IN_FOLDER_URL = 'https://cloud-api.yandex.net:443/v1/disk/resources?limit=500&path=';
    const GET_DONWLOAD_LINK_FOR_FILE_URL = 'https://cloud-api.yandex.net:443/v1/disk/resources/download?path=';
    const CREATE_FOLDER_URL = 'https://cloud-api.yandex.net:443/v1/disk/resources?path=';
    const UPLOAD_FILE_URL = 'https://cloud-api.yandex.net:443/v1/disk/resources/upload?overwrite=true&path=';
    const CHECK_URL = 'https://cloud-api.yandex.net:443/v1/disk/resources?path=';
    const DELETE_URL = 'https://cloud-api.yandex.net:443/v1/disk/resources?force_async=false&path=';
    const URL_UPLOAD_URL = 'https://cloud-api.yandex.net:443/v1/disk/resources/upload?path=';

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
        $files = $this->_checkCache($chapterId);

        if (!$files) {
            $files = $this->_getFilesInFolder($path);
            $this->_addToCache($files, $chapterId);
        }

        return $files;
    }

    public function getChapterLink($chapterId) {
        $path = $this->_convertPath(self::MANGA_FOLDER . '/' . $chapterId);
        return $this->_getDownloadLink($path);
    }

    public function getListOfPages($chapterId) {
        $path = $this->_convertPath(self::MANGA_FOLDER . '/' . $chapterId);
        return $this->_getListOfFiles($path);
    }

    protected function _checkCache($chapterId) {
        return $this->_adapter->getMangaFromCache($chapterId);
    }

    protected function _addToCache($pages, $chapterId) {
        $this->_adapter->addMangaCache($chapterId, $pages);
    }

    protected function _getFilesInFolder($path) {
        $pages = $this->_getListOfFiles($path);
        return $this->_getDownloadLinks($pages);
    }

    protected function _getListOfFiles($path) {
        $path = $this->_convertPath($path);

        $url = self::GET_FILES_IN_FOLDER_URL . $path;
        $context = $this->_getRequestContext();

        $response = file_get_contents($url, false, $context);
        return $this->_parseFilesInFolder($response);
    }

    protected function _getDownloadLink($path) {
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
        $path = $this->_convertPath($path);
        $url = self::CHECK_URL . $path;
        $header = array('Authorization: OAuth ' . YANDEX_TOKEN);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        return $info['http_code'] === 201 || $info['http_code'] === 200;
    }

    public function clearFolder($chapterId) {
        $path = $this->_convertPath(self::MANGA_FOLDER . '/' . $chapterId);

        if ($this->_checkPath($path)) {
            $files = $this->_getListOfFiles($path);
            foreach ($files as $file) {
                $url = self::DELETE_URL . $this->_convertPath($file);
                $header = array('Authorization: OAuth ' . YANDEX_TOKEN);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_exec($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
            }
            $this->_adapter->removeMangaFromCacheByChapterId($chapterId);
            return true;
        }
    }

    public function createFolder($path) {
        $path = $this->_convertPath($path);
        if ($this->_checkPath($path)) {
            return;
        }

        $url = self::CREATE_FOLDER_URL . $path;
        $header = array('Authorization: OAuth ' . YANDEX_TOKEN);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($info['http_code'] !== 201) {
            echo 'Error ' . $info['http_code'];
        }
        if ($info['http_code'] == 409) {
            debug_print_backtrace();
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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $uploadUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($tempName));
        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($info['http_code'] !== 201) {
            echo 'Error2';
        }
    }

    public function uploadFileByUrl($url, $path) {
        $path = $this->_convertPath($path);

        if (strpos($url, 'http://') === false) {
            return;
        }

        $requestUrl = self::URL_UPLOAD_URL . $path . '&url=' . $url;
        var_dump($requestUrl);
        $header = array('Authorization: OAuth ' . YANDEX_TOKEN);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_exec($ch);

        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($info['http_code'] === 201 || $info['http_code'] === 202) {

        }
        else {
            echo ' Error3 ' . $info['http_code'] . PHP_EOL;
        }

    }

    public function clearChapterCache($chapterId) {
        $this->_adapter->removeMangaFromCacheByChapterId($chapterId);
    }

    protected function _convertPath($path) {
        $path = str_replace(' ', '%20', $path);
        return str_replace('/', '%2F', $path);
    }
}
