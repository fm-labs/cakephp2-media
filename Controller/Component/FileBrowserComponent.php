<?php
App::uses('Component','Controller/Component');
App::uses('Folder','Utility');

class FileBrowserComponent extends Component {

/**
 * Controller instance
 * @var Controller
 */
	protected $Controller;

	
	protected $_basePath = WWW_ROOT;
	protected $_baseUrl = '/';
	
	private $__filebrowser = array();
	
	public $components = array('Session');
	
	public function initialize(&$controller) {
		$this->Controller =& $controller;
		$this->basePath($this->basePath());
		$this->baseUrl($this->baseUrl());
	}	
	
	public function startup(&$controller) {
		#$this->layout = 'Media.file_browser';
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
	
	private function __resolveDir() {
		$dir = null;
		
		if (isset($this->Controller->passedArgs['dir'])) {
			$dir = base64_decode($this->Controller->passedArgs['dir']);
		}
		
		if(!$dir || $dir == '.' || $dir == '..')
			$dir = '';
			
		return $dir;
	}
	
	private function __resolveCmd() {

		$cmd = null;
		
		if (isset($this->Controller->passedArgs['cmd'])) {
			$cmd = $this->Controller->passedArgs['cmd'];
		}
		
		return $cmd;
	}
	
	public function read() {
		
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
			'pwd' => $Folder->pwd(),
			'dir' => $dir,
			'dir_encoded' => base64_encode($dir),
			'cmd' => $this->__resolveCmd(),
			'baseUrl' => $this->baseUrl(),
			'directory_list' => $contents[0],
			'file_list' => $contents[1],
		);
		
		return $this->__fileBrowser;
		
	}
	
	public function dispatch() {
		$dispatch = true;
		
		$this->read();
		
		if ($this->__fileBrowser['cmd']) {
			$dispatch = call_user_method($this->__fileBrowser['cmd'],$this);
		}
		
		$this->read();
		
		return $dispatch;
	}
	
	public function upload() {
		$this->Controller->loadModel('Media.FileBrowserUpload');
		
		$Model =& $this->Controller->FileBrowserUpload;
		$Model->Behaviors->load('Media.MeioUpload',array(
			'upload_file' => array(
				'useTable' => false,
				'createDirectory' => false,
				'dir' => $this->__fileBrowser['pwd'],
	 		)
		));
		debug($this->Controller->request->data);
		$upload = $Model->save($this->Controller->request->data);
		debug($upload);
		if ($upload) {
			$this->Session->setFlash(__("Upload successful"));
		} else {
			$this->Session->setFlash(__("Upload failed"));
		}
	}
	
	public function beforeRender(&$controller) {
		#$this->Controller->helpers['Js'] = 'Jquery.JqueryExt';
		$this->Controller->set('fileBrowser',$this->__fileBrowser);
	}
	
}
?>