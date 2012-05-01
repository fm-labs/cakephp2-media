<?php
App::uses('File', 'Utility');

class LibFileBrowserFile {

	private $_path;
	
/**
 * @var LibFileBrowserDir
 */
	public $Dir;
	
/**
 * @var File
 */
	public $File;

/**
 * If true, path is relative to Base not to Dir
 * @var boolean
 */	
	public $isBasePath = true;
	
/**
 * @param string $path Filename relative to Dir
 * @param boolean $isBasePath If true, path is relative to Base not to Dir
 */	
	public function __construct(LibFileBrowserDir &$Dir, $path = null, $isBasePath = true) {
		$this->Dir =& $Dir;
		$this->isBasePath = $isBasePath;
		$this->path($path);
	}

/**
 * @param string $path
 */	
	public function path($path = null) {
		if ($path === null)
			return $this->_path;
			
		$this->_path = $path;
		
		if ($this->isBasePath)
			$_fullPath = $this->Dir->Base->Folder->pwd() . $path;
		else
			$_fullPath = $this->Dir->Folder->pwd() . $path;
			
		$this->File = new File($_fullPath,false);
		
		return $this;
	}
	
	public function exists() {
		if (!is_object($this->File))
			return false;
			
		return $this->File->exists();
	}
	
	public function isImage() {
		return in_array(strtolower($this->File->ext()),array('jpeg','jpg','bmp','png','gif'));
	}
}