<?php
App::uses('LibFileBrowser','Media.FileBrowser');

class FileBrowserComponent extends Component {
	
/**
 * Controller instance
 * @var Controller
 */
	protected $Controller;

/**
 * @var LibFileBrowser
 */	
	public $Browser;
	
	protected $_basePath = TMP;
	
	protected $_dirPath = '/';
	
	protected $_filePath = null;
	
	protected $_cmd = "index";
	
	protected $_renderFile = "";
	
	public $components = array('Media.FileTreeConnector');
	
	public function initialize(&$controller) {
		$this->Controller =& $controller;
			
		
		if (isset($controller->passedArgs['dir']))
			$this->dirPath(base64_decode($controller->passedArgs['dir']));
			
		if (isset($controller->passedArgs['file'])) {
			$this->filePath(base64_decode($controller->passedArgs['file']));
		}		
		
		if (isset($controller->passedArgs['cmd'])) {
			$this->cmd($controller->passedArgs['cmd']);
		}		
		
		if ($this->Controller->request->is('ajax')) {
			$this->Controller->layout = "empty";
			$this->Controller->viewPath = $this->Controller->viewPath.DS."ajax";
		}
		
	}	
	
	public function startup(&$controller) {
	}

	public function beforeRender(&$controller) {
		$controller->set('fileBrowser',$this->Browser);
	}	
	
	public function &start() {
		
		$this->Browser = new LibFileBrowser($this->_basePath, $this->_dirPath, $this->_filePath);
		
		return $this->Browser;
	}

/**
 * Full Path to Base. No slash term 
 * @param string $path
 */	
	public function basePath($path) {
		$this->_basePath = $path;
	}

/**
 * Relative path to Base. Prefix and Suffix Slash term
 * @param string $path
 */	
	public function dirPath($path) {
		$this->_dirPath = $path;
	}
	
/**
 * Relative path to Base. Prefix and Suffix Slash term
 * @param string $path
 */	
	public function filePath($path) {
		$this->_filePath = $path;
	}
	
	public function cmd($cmd = null) {
		if ($cmd === null)
			return $this->_cmd;
			
		$this->_cmd = $cmd;

		switch($cmd) {
			case "connect": 
				if ($this->Controller->request->is('post')) {
					$dirPath = $this->Controller->request->data['dir'];
					$this->dirPath($dirPath);
				}
				break;
				
			default:
				break;
		}
		
		return $this;
	}
	
	public function toArray() {
	}
	
	public function directory() {}
}
?>