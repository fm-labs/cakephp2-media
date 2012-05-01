<?php
App::uses('Folder', 'Utility');

class LibFileBrowserBase {

	private $_path;
	
/**
 * @var Folder
 */
	public $Folder;

/**
 * @param string $path Full path to base
 */	
	public function __construct($path = TMP) {
		$this->path($path);
	}
	
	public function path($path = null) {
		if ($path === null)
			return $this->_path;
			
		$this->_path = $path;
		$this->Folder = new Folder($path,false);
		
		return $this;
	}
}