<?php

class Extension {
	// Хедеры
	/**
	 * тип хедера - link или script
	 * @var string 
	 */
	protected $_type = '';
	/**
	 * ссылка href или src
	 * @var string 
	 */
	protected $_href = '';
	/**
	 * доп. параметр rel
	 * @var string 
	 */
	protected $_rel = ''; 
	/**
	 * доп параметры остальные
	 * @var string[]
	 */
	protected $_attributes = array(); 
	
	public function __construct($type, $href, $rel = '', $attributes = array()) {
		$this->_type = $type;
		$this->_href = $href;
		$this->_rel = $rel;
		$this->_attributes = $attributes;
	}
	
	public function __toString() {
		switch ($this->_type) {
			case '':
				return;
			case 'link':
				$href = $this->_href === '' ? '' : ' href="' . $this->_href . '"';
				foreach ($this->_attributes as $key => $value) {
					$href .= ' ' . $key . '="' . $value . '" ';
				}
				$rel = $this->_rel === '' ? '' : ' rel="' . $this->_rel . '"';
				return '<' . $this->_type . $href . $rel . '>';
			case 'script':
				$src = $this->_href === '' ? '' : ' src="' . $this->_href . '"';
				foreach ($this->_attributes as $key => $value) {
					$src .= ' ' . $key . '="' . $value . '" ';
				}
				return '<' . $this->_type . $src . '></script>';
			default:
				return;
		}
		
	}
}
