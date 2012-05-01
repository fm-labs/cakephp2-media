<?php
App::uses('Folder','Utility');

class FileTreeConnectorComponent extends Component {
	
	protected $_basePath;
	
	protected $_root;
	
	protected $_allowDir = null;
	protected $_denyDir = array();
	
	protected $_allowFile = null;
	protected $_denyFile = null;
	
	
	public function initialize(&$controller) {
		
		$this->basePath(APP);
		$this->root('/');
		
	}
	
	public function basePath($basePath = null) {
		if ($basePath === null)
			return $this->_basePath;
			
		$this->_basePath = $basePath;
		return $this;
	}
	
	public function root($root = null) {
		if ($root === null)
			return $this->_root;

		/*
		if ($root[0] == "/" || $root[0] == "\\")
			$root = substr($root,1);	
		*/
				
		$this->_root = $root;
		return $this;
	}
	
	public function read($sort = true, $exceptions = array(), $fullPath = false) {
		$path = $this->getPath();
		
		if (!is_dir($path)) {
			return array();
		}
		
		$Folder = new Folder($path,false);
		$contents = $Folder->read($sort, $exceptions, $fullPath);
		
		return $contents;
	}
	
	public function search($regex = ".*", $sort = false) {
		$path = $this->getPath();
		
		if (!is_dir($path)) {
			return false;
		}
		
		$Folder = new Folder($path,false);
		return $Folder->find($regex, $sort);
	}
	
	public function getPath() {
		return $this->basePath().$this->root();
	}
	
	/***** NOT IN USE FUNCTIONS ******/

	public function allow($mode, $pattern, $merge = true) {
		$this->_parsePermission($mode, true, $pattern);
	}
	
	public function deny($mode, $pattern, $merge = true) {
		$this->_parsePermission($mode, false, $pattern, $merge);
	}
	
	private function _parsePermission($mode, $allow, $pattern, $merge = true) {
		if ($mode == 'file') {
			($allow == true) ? $_target =& $this->_allowFile : $_target =& $this->_denyFile;
		} else {
			($allow == true) ? $_target =& $this->_allowDir : $_target =& $this->_denyDir;
		}
		
		if (!is_array($pattern))
			$pattern = array($pattern);
			
		if ($merge)
			$_target = array_merge($_target,$pattern);
		else
			$_target = $pattern;
	}
		
	
}
?>