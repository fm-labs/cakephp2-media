<?php
App::uses('Folder', 'Utility');

class LibFileBrowserDir {

	private $_path;
	
/**
 * @var LibFileBrowserBase
 */
	public $Base;
	
/**
 * @var Folder
 */
	public $Folder;

/**
 * @param string $path Full path to base
 */	
	public function __construct(LibFileBrowserBase &$Base, $path = '/') {
		$this->Base =& $Base;
		$this->path($path);
	}
	
	public function path($path = null) {
		if ($path === null)
			return $this->_path;
			
		$this->_path = $path;
		
		$_fullPath = $this->Base->Folder->pwd() . $path;
		$this->Folder = new Folder($_fullPath,false);
		
		return $this;
	}
	
	public function parentPath($path = null) {
		if (!$path)
			$path = $this->path();
			
		return substr($this->path(),0,strrpos(substr($this->path(),0,-1), '/')+1);
	}
	
	public function inWebroot() {
		return $this->Folder->inPath(WWW_ROOT);
	}
}