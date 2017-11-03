<?php

class MangaDataAdapter extends DataAdapter {
    const COL_ID = 'id';
    const COL_NAME = 'name';
    const COL_FOLDER = 'folder';
    
    public function getAllMangas() {
        $sql = $this->_mysqli->prepare('SELECT id, name from manga');
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
    
    public function getMangaById($id) {
        $sql = $this->_mysqli->prepare('SELECT name, folder from manga WHERE id=?');
        $sql->bind_param('i', $id);
        $status = $sql->execute();
		
		if ($status) {
            $result = $sql->get_result();
			$row = $result->fetch_assoc();
			$result->free();
			
			return $row;
		}
        
		return false;
    }
    
    public function updateManga($id, $name, $folder) {
        $sql = $this->_mysqli->prepare('UPDATE `manga` SET `name`=?,`folder`=? WHERE `id`=?');
        $sql->bind_param('ssi', $name, $folder, $id);
        $status = $sql->execute();
        
        return $status;
    }
    
    public function createNewManga($name, $folder) {
        $sql = $this->_mysqli->prepare('INSERT INTO `manga`(`id`, `name`, `folder`) VALUES(NULL,?,?)');
        $sql->bind_param('ss', $name, $folder);
        $status = $sql->execute();
        
        return $status;
    }
    
    public function removeManga($id) {
        $sql = $this->_mysqli->prepare('DELETE FROM `manga` WHERE `id` = ?');
        $sql->bind_param('i', $id);
        $status = $sql->execute();
        
        return $status;
    }
}
