<?php
App::uses('Component','Controller/Component');
App::uses('Folder','Utility');
App::uses('File','Utility');

/**
 * FileBrowserComponent
 * 
 * @author Flow
 * @property SessionComponent $Session
 */
class FileBrowserComponent extends Component {

	
	public $components = array('Session');
	
/**
 * Controller instance
 * @var Controller
 */
	protected $Controller;

/**
 * Absolute path to file root
 * @var string
 */	
	protected $_basePath = WWW_ROOT;
	
/**
 * Base url relative to FULL_BASE_URL
 * @var string
 */
	protected $_baseUrl = '/';

/**
 * Current FileBrowser
 * @var mixed
 */	
	private $__fileBrowser = array();


/**
 * Currently used config
 * @var string Default 'default'
 */	
	private $__config = 'default';
	
/**
 * User Command
 * Supported commands: open|parent_dir|file_rename|file_delete
 * 
 * @var string Default 'open'
 */	
	private $__cmd 		= "open";
	
/**
 * Current selected file name in format: [filename].[ext]
 * @var string Default NULL
 */
	private $__file 	= null;
	
/**
 * Current selected directory in format [directoryname]/
 * with trailing slash!
 * @var string Default '/'
 */
	private $__dir 		= "/";
	
/**
 * Last error
 * @var string
 */
	private $__error 	= "";
	
/**
 * Name of view file to be rendered after dispatching
 * @var string Default 'index'
 */
	private $__view 	= "index";
	
/**
 * @see Component::initialize()
 */
	public function initialize(&$controller) {
		$this->Controller =& $controller;
		$this->basePath($this->basePath());
		$this->baseUrl($this->baseUrl());
		
	}	

/**
 * @see Component::beforeRender()
 */
	public function beforeRender(&$controller) {
		$this->Controller->set('fileBrowser',$this->__fileBrowser);
	}	
	
	public function config($name = 'default') {
		$config = Configure::read('FileBrowser.'.$name);
		if (!$config)
			throw new CakeException(__d('media',"FileBrowserComponent::config() No configuration '%s' found",$name));
			
		$this->__config = $name;
		foreach($config as $key => $val) {
			switch($key) {
				case "basePath":
					$this->basePath($val);
					break;
				case "baseUrl":
					$this->baseUrl($val);
					break;
				default:
					break;
			}
		}
	}
	
	public function basePath($path = null) {
		if (is_null($path))
			return $this->_basePath;
			
		$this->_basePath = $path;
	}
	
	public function baseUrl($url = null) {
		if (is_null($url))
			return $this->_baseUrl;
			
		$this->_baseUrl = Router::url('/'.$url);
	}		

/**
 * Initalizes params from Controller request
 * @return void
 */	
	protected function _initRequest() {
		$dir = $file = $cmd = null;
		
		//dir
		if (isset($this->Controller->passedArgs['dir'])) {
			$dir = base64_decode($this->Controller->passedArgs['dir']);
		}
		if(!$dir || $dir == '.' || $dir == '..')
			$dir = '';
			
		$this->__dir = $dir;
		
		//file
		if (isset($this->Controller->passedArgs['file'])) {
			$file = base64_decode($this->Controller->passedArgs['file']);
		}
		$this->__file = $file;
	
		//cmd
		if (isset($this->Controller->passedArgs['cmd'])) {
			$cmd = $this->Controller->passedArgs['cmd'];
		}
		$this->__cmd = $cmd;
		
		//filepath
		if (isset($this->Controller->passedArgs['filepath'])) {
			$filepath = base64_decode($this->Controller->passedArgs['filepath']);
			$parts = explode('/',$filepath);
			$this->__file = array_pop($parts);
			$this->__dir = (count($parts) > 0) ? join('/',$parts).'/' : '';
		}
	}

/**
 * Dispatches the selected command
 * @return void
 * @throws CakeException
 */	
	public function dispatch() {
		
		$this->_initRequest();
		
		$dispatch = true;
		
		$exception = null;
		if ($this->__cmd) {
			$cmdMethod = "_cmd".Inflector::camelize($this->__cmd);
			try {
				if (!method_exists($this, $cmdMethod))
					throw new CakeException(__d('media',"Unknown FileBrowser Command '%s'",strval($this->__cmd)));

				$dispatch = call_user_method($cmdMethod,$this);
				if (!is_null($dispatch))
					return $dispatch;
					
			} catch(Exception $e) {
				$exception = $e;
				CakeLog::write('error', 'FileBrowserComponent::dispatch() '.$exception->getMessage());
				$this->__error = $exception->getMessage();
			}
		}
		
		$this->__fileBrowser = $this->_buildFileBrowser();
		
		if ($exception) {
			$this->Controller->Session->setFlash($exception->getMessage());
		} elseif ($this->__fileBrowser['FileBrowser']['error']) {
			$this->Controller->Session->setFlash($this->__fileBrowser['FileBrowser']['error']);
		}
		
		if (!$this->__view)
			$this->__view = "index";
			
		$_view = 'Media.FileBrowser/admin_'.$this->__view;
		
		$this->Controller->autoRender = false;
		$this->Controller->render($_view, 'Media.filebrowser');
		
		return $dispatch;
	}	
	
	
	public function getFileBrowser() {
		return $this->__fileBrowser;
	}
	
	public function _cmdOpen() {
		
		$__file = null;
		return null;
	}
	
	public function _cmdParentDir() {
		
		$dir = $this->__dir;
		
		
		$this->__dir = self::parentDir($dir);
		$this->__file = null;
		return null;
	}
	
	public function _cmdFileDelete() {
		
		$File = $this->_getFile();
		
		if (!$File)
			throw new CakeException(__d('media',"Failed to delete file. No file specified"));

		$_fileName = $File->name().".".$File->ext();
		if ($File->delete()) {
			$this->Controller->Session->setFlash(__d('media',"File '%s' deleted", $_fileName));
		} else
			throw new CakeException(__d('media',"Failed to delete file '%s'", $_fileName));


		$this->__file = null;	
			
		return null;
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
			
		if (!$file)
			return false;	
			
		$filePath = $this->basePath().$this->__dir.$file;
		if (!file_exists($filePath))
			return false;
			
		return new File($filePath, false);
	}
	
	public function _cmdUpload() {
		$this->Controller->loadModel('Media.FileBrowserUpload');
		
		$Model =& $this->Controller->FileBrowserUpload;
		$Model->Behaviors->load('Media.MeioUpload',array(
			'upload_file' => array(
				'useTable' => false,
				'createDirectory' => false,
				'dir' => $this->basePath().$this->__dir,
	 		)
		));
		$upload = $Model->save($this->Controller->request->data);
		if ($upload) {
			$_data = $Model->data[$Model->alias];
			$this->Controller->Session->setFlash(__d('media',"Successfully uploaded as '%s'",$_data['name']));
			$this->__file = $_data['name'];
		} else {
			throw new CakeException(__d('media',"Upload failed"));
		}
		
		return null;
	}	
	
	public function _cmdFileRename() {
		
		$this->__view = "file_rename";
		if ($this->Controller->request->is('post') || $this->Controller->request->is('put')) {
			$_data = &$this->Controller->request->data;
			
			$filePath1 = $_data['FileBrowser']['dir'].$_data['FileBrowser']['file'];
			$filePath2 = $_data['FileBrowser']['dir'].$_data['FileBrowser']['file_new'];
			
			if ($filePath1 == $filePath2)
				throw new CakeException(__d('media',"File '%s' not renamed", $filePath1));
			
			
			if (!file_exists($this->basePath().$filePath1))
				throw new CakeException(__d('media',"File '%s' not found", $filePath1));
				
			if (file_exists($this->basePath().$filePath2))
				throw new CakeException(__d('media',"File '%s' already exists", $filePath2));

			if (!rename($this->basePath().$filePath1, $this->basePath().$filePath2))
				throw new CakeException(__d('media',"Failed to rename '%s' to '%s'",$filePath1, $filePath2));
				
			$this->__file = $_data['FileBrowser']['file_new'];
			$this->__cmd = null;
			$this->__view = null;
				
		} 
		
		return null;
	}
	
	protected function _buildFileBrowser($fileBrowser = array(),$params = array()) {
		
		$_fileBrowser = array_merge(array(
			'config' => $this->__config,
			'basePath' => $this->basePath(),
			'baseUrl' => $this->baseUrl(),
			//'cwd' => null,
			'cmd' => $this->__cmd,
			'dir' => $this->__dir,
			//'dir_encoded' => null,
			'file' => $this->__file,
			//'file_encoded' => null,
			'error' => $this->__error,
		),$fileBrowser);
		
		$BaseFolder = new Folder($_fileBrowser['basePath'],false);
		$Folder = clone($BaseFolder); //clone
		
		if ($_fileBrowser['dir']) {
			$dir = $_fileBrowser['dir'];
			if (!$Folder->cd($dir)) {
				$_fileBrowser['error'] = __d('media',"Directory %s not found",strval($_fileBrowser['dir']));
				do {
					$dir = self::parentDir($dir);
					if ($Folder->cd($dir)) {
						break;
					}
				} while(!is_null($dir));
				
			}
			$_fileBrowser['dir'] = $dir;
			
			if (!$Folder->inPath($BaseFolder->pwd())) {
				$_fileBrowser['error'] = __d('media',"Directory %s not accessable (Not in %s)",strval($_fileBrowser['dir']), $BaseFolder->pwd());
			}
		}
		
		$contents = $Folder->read(true,array('.','empty'));
		
		$fileBrowser = array(
			'FileBrowser' => $_fileBrowser,
			'Folder' => $contents[0],
			'File' => $contents[1]
		);
		
		return $fileBrowser;
	}


	
	static public function parentDir($dir) {
		if (!$dir || $dir == '/' || $dir == DS)
			return null;
			
		$_dir = explode('/', substr($dir, 0,-1));
		array_pop($_dir);
		if (count($_dir) > 0) {
			$dir = join('/',$_dir) . '/';
		} else {
			$dir = null;
		}
		return $dir;
	}

	
}
?>