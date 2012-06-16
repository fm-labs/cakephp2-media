<?php
App::uses('Component','Controller/Component');
App::uses('Folder','Utility');
App::uses('File','Utility');

class FileBrowserComponent extends Component {

/**
 * Controller instance
 * @var Controller
 */
	protected $Controller;

	
	protected $_basePath = WWW_ROOT;
	protected $_baseUrl = '/';
	
	private $__fileBrowser = array();
	
	private $__cmd;
	private $__file;
	private $__dir;
	
	public $components = array('Session');
	
	public function initialize(&$controller) {
		$this->Controller =& $controller;
		$this->basePath($this->basePath());
		$this->baseUrl($this->baseUrl());
		
		$this->__resolveCmd();
		$this->__resolveDir();
		$this->__resolveFile();
	}	
	
	public function startup(&$controller) {
		#$this->layout = 'Media.file_browser';
	}

	public function beforeRender(&$controller) {
		#$this->Controller->helpers['Js'] = 'Jquery.JqueryExt';
		$this->Controller->set('fileBrowser',$this->__fileBrowser);
	}	


	public function read($params = array()) {
		
		$this->__fileBrowser = $this->buildFileBrowser(
			$this->basePath(), 
			$this->baseUrl(), 
			$this->__dir,
			$this->__cmd, 
			$params
		);
		
		return $this->__fileBrowser;
		
	}	
	
	public function _cmdFileDelete() {
		
		$File = $this->_getFile();
		
		if (!$File)
			throw new CakeException(__("Failed to delete file. No file specified"));

		$_fileName = $File->name().".".$File->ext();
		if ($File->delete()) {
			$this->Controller->Session->setFlash(__("File '%s' deleted", $_fileName));
			return null;
		} else {
			$this->Controller->Session->setFlash(__("Failed to delete file '%s'", $_fileName));
		}
		return false;
	}

/**
 * 
 * Enter description here ...
 * @param unknown_type $file
 * @return File
 */	
	protected function _getFile($file = null) {
		if (!$file)
			$file = $this->__file;
			
		$filePath = $this->basePath().$this->__dir.$file;
		return new File($filePath, false);
	}
	
	public function _cmdUpload() {
		$this->Controller->loadModel('Media.FileBrowserUpload');
		
		$Model =& $this->Controller->FileBrowserUpload;
		$Model->Behaviors->load('Media.MeioUpload',array(
			'upload_file' => array(
				'useTable' => false,
				'createDirectory' => false,
				'dir' => $this->__fileBrowser['cwd'],
	 		)
		));
		$upload = $Model->save($this->Controller->request->data);
		if ($upload) {
			$this->Session->setFlash(__("Upload successful"));
			return null;
		} else {
			$this->Session->setFlash(__("Upload failed"));
		}
		return false;
	}	
	
	private function __resolveFile() {
		$file = null;
		
		if (isset($this->Controller->passedArgs['file'])) {
			$file = base64_decode($this->Controller->passedArgs['file']);
		}
		
		$this->__file = $file;
	}
	
	private function __resolveDir() {
		$dir = null;
		
		if (isset($this->Controller->passedArgs['dir'])) {
			$dir = base64_decode($this->Controller->passedArgs['dir']);
		}
		
		if(!$dir || $dir == '.' || $dir == '..')
			$dir = '';
			
		$this->__dir = $dir;
	}
	
	private function __resolveCmd() {

		$cmd = null;
		
		if (isset($this->Controller->passedArgs['cmd'])) {
			$cmd = $this->Controller->passedArgs['cmd'];
		}
		
		$this->__cmd = $cmd;
	}


	public function basePath($path = null) {
		if (is_null($path))
			return $this->_basePath;
			
		$this->_basePath = $path;
	}
	
	public function baseUrl($url = null) {
		if (is_null($url))
			return $this->_baseUrl;
			
		$this->_baseUrl = Router::url($url);
	}	
	
	public function buildFileBrowser($basePath = WWW_ROOT, $baseUrl = '/', $dir = '', $cmd = 'index', $params = array()) {
		
		$BaseFolder = new Folder($basePath,false);
		$Folder = clone($BaseFolder); //clone
		
		if ($dir) {
			if (!$Folder->cd($dir)) {
				throw new CakeException(__("Directory %s not found",strval($dir)));
			}
			if (!$Folder->inPath($BaseFolder->pwd())) {
				throw new CakeException(__("Directory %s not accessable",strval($dir)));
			}
		}
		
		$contents = $Folder->read(true,array('.','empty'));
		
		$fileBrowser = array(
			'basePath' => $basePath,
			'baseUrl' => $baseUrl,
			'cwd' => $Folder->pwd(),
			'dir' => $dir,
			'dir_encoded' => base64_encode($dir),
			'cmd' => $cmd,
			'baseUrl' => $baseUrl,
			'folders' => $contents[0],
			'files' => $contents[1],
		);
		
		return $fileBrowser;
	}
	
	public function dispatch() {
		$dispatch = true;
		
		$this->read();
		
		if ($this->__cmd) {
			$cmdMethod = "_cmd".Inflector::camelize($this->__cmd);
			if (!method_exists($this, $cmdMethod))
				throw new CakeException(__("Unknown FileBrowser Command '%s'",strval($this->__cmd)));

			try {
				$dispatch = call_user_method($cmdMethod,$this);
				if (!is_null($dispatch))
					return $dispatch;
					
			} catch(Exception $e) {
				$this->__fileBrowser['error'] = $e->getMessage();
				throw new CakeException($e->getMessage());
			}
		}
		
		$this->read();
		
		return $dispatch;
	}
	
	public function fileBrowser() {
		return $this->__fileBrowser;
	}

	
}
?>