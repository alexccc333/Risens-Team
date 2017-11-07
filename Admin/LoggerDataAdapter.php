<?php
class LoggerDataAdapter extends DataAdapter {
    const COL_ID = 'id';
    const COL_DATE = 'date';
    const COL_VALUE = 'value';
    const COL_USER = 'user_id';
    
    const PAGE_OFFSET = 30;
    
    public function insertLog($userId, $data) {
        $sql = $this->_mysqli->prepare('INSERT INTO `admin_log`(`id`, `date`, `user_id`, `value`) VALUES(NULL,NULL,?,?)');
        $sql->bind_param('is', $userId, $data);
        $status = $sql->execute();
        
        return $status;
    }
    
    public function getLogs($page) {
        $sql = $this->_mysqli->prepare('SELECT * FROM admin_log ORDER BY id DESC LIMIT ?,' . self::PAGE_OFFSET);
        $page = $page * self::PAGE_OFFSET;
        $sql->bind_param('i', $page);
        
        $status = $sql->execute();
        
        if ($status) {
            $result = $sql->get_result();
            $returnArray = array();
            
			$row = $result->fetch_assoc();
            while ($row) {
                $returnArray[] = $row;
                $row = $result->fetch_assoc();
            }
			$result->free();
            
			return $returnArray;
        }
    }
    
    public function getRowsCount() {
        $sql = $this->_mysqli->prepare('SELECT COUNT(1) FROM admin_log');
        $status = $sql->execute();
        
        if ($status) {
            $result = $sql->get_result();
            $row = $result->fetch_assoc();
            $count = array_shift($row);
            return ceil($count / self::PAGE_OFFSET);
        }
    }
}
