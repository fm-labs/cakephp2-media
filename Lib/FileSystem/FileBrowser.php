<?php
App::uses('Router','Routing');
App::uses('Folder','Utility');
App::uses('File','Utility');

/**
 * FileBrowserComponent
 * 
 * @author Flow
 * @property SessionComponent $Session
 */
class FileBrowser {

	protected $_config = array(
		'basePath' => WWW_ROOT,
		'baseUrl' => '/'		
	);
	
	protected $_configName = 'default';
	
	/**
	 * Current selected file name in format: [filename].[ext]
	 * @var string Default NULL
	 */
	protected $_path = array();
	
	protected $_file = '';
	
	
	public $Folder;
	
	/**
	 * Create Instance of FileBrowser
	 * 
	 * @param array|string $config
	 * @throws CakeException
	 */
	public function __construct($config = array(), $path = '/') {
		$this->config($config);
		$this->init();
		$this->openDir($path);
	}
	
	/**
	 * Getter/Setter for FileBrowser configuration
	 * 
	 * @param array|string $config List of config options or a config name which points to 'Media.FileBrowser.$config'
	 * @return array|FileBrowser Returns current stored config if $config was NULL. Otherwise FileBrowser instance
	 */
	public function config($config = null) {
		
		if (is_null($config))
			return $this->_config;
		
		$_config = Configure::read('Media.FileBrowser.'.$config);
		
		if (!$_config)
			throw new CakeException(__('FileBrowser config %s not found',strval($config)));
		
		$this->_configName = $config;
		
		foreach((array)$_config as $key => $val) {
			
			if (!array_key_exists($key, $this->_config))
				continue;
				
			if (method_exists($this, $key)) {
				$this->{$key}($val);
			} else {
				$this->_config[$key] = $val;
			}
		}
		
		return $this;
	}
	
	public function set($path, $file = null) {
		$this->setPath($path);
		$this->setFile($file);
	}
	
	public function setPath($path, $check = false) {
		
		if (is_string($path))
			$path = explode('/',$path);
		
		if (!empty($path)) {
			if ($path[0] == '')
				array_shift($path);
			elseif ($path[0] == '..') {
				$_path = $this->_path;
				array_pop($_path);
				$path = $_path;
			}
		}
		#if ($check && !is_dir($this->_dirPath($path)))
		#	throw new CakeException("setDir(): $path is not a directory");
		
		$this->_path = $path;
	}
	
	public function getPath() {
		return $this->_path;
	}
	
	public function getDir() {
		return join('/', $this->_path);
	}
	
	public function setFile($file, $check = false) {
		
		#if ($check && !is_file($this->_filePath($file)))
		#	throw new CakeException("setFile(): $file is not a file");
		
		$this->_file = $file;
	}
	
	public function basePath($path = null) {
		if (is_null($path))
			return $this->_config['basePath'];
			
		$this->_config['basePath'] = $path;
		return $this;
	}
	
	public function baseUrl($url = null) {
		if (is_null($url))
			return $this->_config['baseUrl'];
			
		$this->_config['baseUrl'] = Router::url('/'.$url);
		return $this;
	}
	
	public function init() {
		$this->Folder = new Folder($this->basePath(), false);
	}
	
	public function openDir($dir) {
		$this->setPath($dir);
	}
	
	public function parentDir() {
		return $this->openDir('..');
	}
	
	public function removeDir() {
		
	}
	
	public function moveDir() {
		
	}
	
	public function copyDir() {
		
	}
		
	/**
	 * Getter / Setter for current path
	 * @param string $path
	 * @return string
	 */
	public function _setPath($path = null) {
		if (!$path || $path == '.')
			return $this->_path;
		
		if (substr($path,0,1) == '/') {
			$this->_path = "";
			return $this->setPath(substr($path,1));	
		}
		
		if ($path == '..') {
			$parts = explode('/', $this->_path);
			if (empty($parts))
				return $this->_path;
				
			$last = array_pop($parts);
			array_pop($parts);
			if ($last == '') {
				array_push($parts, $last);
			}
			$path = join('/',$parts);
			$this->_path = $this->_dir = "";
			return $this->setPath($path);
		} 
		
		$parts = explode('/', $path);
		
		$last = array_pop($parts);
		if ($last != '/') {
			$file = $last;
			$path = join('/',$parts);
			$dir = array_pop($parts);
		}
		
		$path = $this->_path . $path;
		
		if (strlen($path) > 0 && substr($path,-1) != '/')
			$path .= '/';
		
		$this->_dir = $dir;
		$this->_file = $file;
		$this->_path = $path;
		return $path;
	}
	
	public function path() {
		return $this->_path;
	}
	
	public function dir() {
		return $this->_dir;
	}
	
	public function file() {
		return $this->_file;
	}
	
	public function readFile() {
		$File = new File($this->_filePath());
		return $File->read();
	}
		
	public function deleteFile() {
		return unlink($this->_filePath());
	}
	
	public function copyFile() {
		$info = pathinfo($this->_filePath());
		$i = 1;
		do {
			$fileCopy = $info['filename'].'_copy_'.$i++.'.'.$info['extension'];
			
			if (!$this->exists($this->_filePath($fileCopy)))
				break;
			
		} while(true);
		
		if (copy($this->_filePath(),$this->_filePath($fileCopy))) {
			$this->_file = $fileCopy;
			return true;
		}
		
		return false;
	}
	
	public function renameFile($fileNew) {
		
		if ($this->exists($this->_filePath($fileNew)))
			throw new CakeException(__("File %s already exists",$fileNew));
		
		if (!copy($this->_filePath(), $this->_filePath($fileNew)))
			throw new CakeException(__("Failed to copy file %s to %s",$this->_file,$fileNew));
		
		if (!$this->deleteFile())
			throw new CakeException(__("Failed to delete file",$this->_file));
		
	}
	
	protected function exists($filePath) {
		return file_exists($filePath);
	}
	
	protected function _dirPath($dir = null) {
		if (null === $dir)
			$dir = $this->getDir();
		
		return $this->_config['basePath'].$dir.'/';
	}
	
	protected function _filePath($file = null) {
		if (null === $file)
			$file = $this->_file;
		
		return $this->_dirPath() . $file;
	}
	
	public function toArray() {
	
		$BaseFolder = new Folder($this->_dirPath(),false);
		$Folder = clone($BaseFolder); //clone
	
		$contents = $Folder->read(true,array('.','empty'));
	
		return array(
			'FileBrowser' => array(
				'config' => $this->_configName,
				'basePath' => $this->_config['basePath'],
				'baseUrl' => $this->_config['baseUrl'],
				'dir' => $this->getDir(),
				'path' => $this->getPath(),
				'file' => $this->file(),
			),
			'Folder' => $contents[0],
			'File' => $contents[1]
		);
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
	
	
	
	
}
?>