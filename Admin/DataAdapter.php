<?php

class DataAdapter {
    protected $_mysqli = null;
		
	public function __construct($mysqli) {
		$this->_mysqli = $mysqli;
	}
}
