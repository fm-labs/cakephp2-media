<?php
App::uses('Component','Controller/Component');

class FileBrowserComponent extends Component {

/**
 * Controller instance
 * @var Controller
 */
	protected $Controller;

	
	protected $_basePath = WWW_ROOT;
	protected $_baseUrl = '/';
	
	private $__filebrowser = array();
	
	public function initialize(&$controller) {
		$this->Controller =& $controller;
		$this->basePath($this->basePath());
		$this->baseUrl($this->baseUrl());
	}	
	
	public function startup(&$controller) {
		#$this->layout = 'Media.file_browser';
	}

	public function basePath($path = null) {
		if (!$path)
			return $this->_basePath;
			
		$this->_basePath = $path;
	}
	
	public function baseUrl($url = null) {
		if (!$url)
			return $this->_baseUrl;
			
		$this->_baseUrl = Router::url($url);
	}
	
	private function __resolveDir($dir = null) {
		if ($dir)
			return $dir;

		if (isset($this->Controller->passedArgs['dir'])) {
			$dir = base64_decode($this->Controller->passedArgs['dir']);
		}
		
		if($dir == '.' || $dir == '..')
			$dir = null;
			
		return $dir;
	}
	
	public function read($dir = null) {
		
		$BaseFolder = new Folder($this->basePath(),false);
		$Folder = $BaseFolder; //clone
		
		$dir = $this->__resolveDir();
		if ($dir) {
			if (!$Folder->cd($dir)) {
				throw new CakeException(__("Directory %s not found",strval($dir)));
			}
			if (!$Folder->inPath($BaseFolder->pwd())) {
				throw new CakeException(__("Directory %s not accessable",strval($dir)));
			}
		}
		
		$contents = $Folder->read(true,array('.','empty'));
		
		$this->__fileBrowser = array(
			'dir' => $dir,
			'baseUrl' => $this->baseUrl(),
			'directory_list' => $contents[0],
			'file_list' => $contents[1],
		);
		
		return $this->__fileBrowser;
		
	}
	
	
	public function beforeRender(&$controller) {
		$this->Controller->set('fileBrowser',$this->__fileBrowser);
	}
	
}
?>