<?php
App::uses('Folder','Utility');
App::uses('File','Utility');

/**
 * FileManager Component
 * 
 * @package cake.plugins
 * @subpackage cake.plugins.media.components.filemanager
 * 
 * @author Flow
 * @property Folder $_Folder
 */
class FileManagerComponent extends Component {

/**
 * Base path without trailing DS
 * @var string
 */	
	public $basePath = WWW_ROOT;

	public $actionMap = array(
		'dir' => 'index',
		'file' => 'file'
	);
	
/**
 * Relative directory
 * @var string
 */	
	protected $_dir = '/';
	
/**
 * Absolute path to directory
 * @var string
 */	
	protected $_dirPath;

/**
 * Absolute path to parent directory of $_dirPath. Null if is root-directory
 * @var string
 */	
	protected $_parentDir;

/**
 * File name
 * @var string
 */	
	protected $_file;

/**
 * Absolute path to file
 * @var string
 */	
	protected $_filePath;
	

/**
 * Holds request arguments
 * @var mixed
 */	
	private $_args = array();

/**
 * Holds exception which was thrown will startup
 * This will be thrown again when directory gets opened
 * @var Exception
 */	
	private $__cachedException;
	
/**
 * Instance of Folder object
 * @var Folder
 */	
	private $_Folder;

/**
 * Instance of File object
 * @var File
 */
	private $_File;
	
	public function __construct(&$collection, $settings = array()) {
		
		$settings = array_merge(array(
			'basePath' => $this->basePath,
			'dir' => $this->_dir,
		),$settings);
		
		foreach ($settings as $key => $val){
			if (method_exists($this,$key)) {
				call_user_method($key,$this, $val);
			}
		}
	}
	
/**
 * Initialize component
 * Config can overwrite public class properties
 * @see Component::initialize()
 */	
	public function initialize(Controller &$controller) {
	}

/**
 * Startup component
 * Load arguments from request
 * 
 * @see Component::startup()
 */	
	public function startup(Controller &$controller) {
		try {
			$this->setArgs($controller->passedArgs);
		} catch (Exception $e) {
			$this->__cachedException = $e;
		}
	}

/**
 * Get/Set absolute basePath
 * 
 * @access public
 * @param string $path
 * @return FileManagerComponent
 * @throws FileManagerFolderNotFoundException
 */	
	public function basePath($path = null) {
		if (is_null($path))
			return $this->basePath;
			
		#$path = preg_replace('@([\\]|[\/])$@','',$path);
		$realPath = realpath($path);
		if (!$realPath)
			throw new FileManagerFolderNotFoundException($path);
		
		$this->basePath = $realPath;
		
		return $this;
	}

/**
 * Get/Set directory relative to basePath with trailing slash
 * 
 * @access public
 * @param string $dir
 * @return FileManagerComponent
 * @throws FileManagerFolderNotFoundException
 */	
	public function dir($dir = null) {
		if (is_null($dir))
			return $this->_dir;

		//dir
		//check trailing dir separator
		if (!preg_match('@^([\\]|[\/])@',$dir)) {
			$dir = '/'.$dir;
		}
		if (!preg_match('@([\\]|[\/])$@',$dir)) {
			$dir = $dir.'/';
		}
		
		//dirPath
		$dirPath = $this->basePath().$dir;
		$realPath = realpath($dirPath);
		if (!$realPath)
			throw new FileManagerFolderNotFoundException($dirPath);
			
		$Folder = new Folder($realPath);
		if (!$Folder->inPath($this->basePath()))
			throw new CakeException(__("Not allowed to access $realPath"));

		$this->_dir = $dir;
		$this->_dirPath = Folder::slashTerm($realPath);
		
		//parentDir
		$this->_parentDir = "/";
		$_parts = explode('/',$dir);
		if (count($_parts) <= 1)
			$this->_parentDir = '/';
		else {
			unset($_parts[count($_parts)-2]);
			$this->_parentDir = join('/',$_parts);
		}
			
		return $this;
	}

/**
 * Returns absolute directory path
 * 
 * @access protected
 * @return string Returns null if directory hasn't been set
 */	
	protected function dirPath() {
		return $this->_dirPath;
	}
		
/**
 * Set arguments
 * 
 * @access public
 * @param mixed $args
 * @return FileManagerComponent
 */	
	public function setArgs($args = array()) {
		
		return $this;
		
		//defaults
		$args = array_merge(array(
			'mode' => null,
			'cmd' => null,
			'dir' => null,
			'dirEncoded' => true,
			'file' => null,
			'fileEncoded' => true,
		),$args);

		$this->_args = $args;
		
		//set directory
		if (!$args['dir']){
			$args['dir'] = $this->dir();
			$args['dirEncoded'] = false;
		}
		if ($args['dirEncoded']) {
			$args['dir'] = self::decodePath($args['dir']);
		}
		$this->dir($args['dir']);
		
		//set file
		if ($args['file'] && $args['fileEncoded']) {
			$args['file'] = self::decodePath($args['file']);
		}
		$this->file($args['file']);
		
		return $this;
	}
	
/**
 * Open directory
 * 
 * @access public
 * @param string $dir Path relative to basePath
 * @return FileManagerComponent
 * @throws FileManagerFolderNotFoundException
 */	
	public function openDir($dir = null) {
		if ($this->__cachedException)
			throw $this->__cachedException;
		
		if ($dir !== null)
			$this->dir($dir);
		
		$dirPath = $this->dirPath();
		$Folder = new Folder();
		if (!$Folder->cd($dirPath))
			throw new FileManagerFolderNotFoundException($dirPath);
			
		$this->_Folder = $Folder;	
		
		return $this;
	}

/**
 * Close directory
 * 
 * @access public
 * @return FileManagerComponent
 */	
	public function closeDir() {
		$this->_Folder = null;
		
		return $this;
	}

/**
 * Indicates if directory has been opened
 * 
 * @access public
 * @return boolean True, if directory has been opened
 */	
	public function isDirOpened() {
		return (is_null($this->_Folder)) ? false : true;
	}
	
/**
 * Return directory contents
 * 
 * @access public
 * @param mixed $options
 * @return mixed Like Folder::read()
 * @see Folder::read()
 */	
	public function getDirContents($options = array()) {
		
		$options = array_merge(array(
			'depth' => 1,
			'fullPath' => false,
			'sort' => false,
			'exceptions' => array()
		),$options);
		
		if (!$this->isDirOpened())
			return false;
			
		return $this->_Folder->read($options['sort'],$options['exceptions'],$options['fullPath']);
	}
	
	public function file($file = null) {
		if (is_null($file))
			return $this->_file;

		$file = basename($file);
		$filePath = $this->dirPath().$file; //use basename to prevent path-injection
		$realPath = realpath($filePath);
		if (!$realPath)
			throw new CakeException(__("File '%s' not found or not accessable",$filePath));
			
		$this->_file = $file;
		$this->_filePath = $realPath;
			
	}

	public function openFile($file = null) {
		if ($file !== null)
			$this->file($file);
			
		$File = new File();
		if (!$File->open($this->_filePath))
			throw new CakeException(__("Failed to open file '%s' in '%s'",$this->file(),$this->_filePath));
			
		$this->_File = $File;
		
		return $this;
	}
	
/**
 * Extract Object to array
 * 
 * @access public
 * @param mixed $contentOptions These will be passed to the getDirContents() function
 * @return mixed
 */	
	public function toArray($contentOptions = array()) {
		
		if (!$this->isDirOpened()) {
			return array();
		}
		
		$contents = $this->getDirContents($contentOptions);
		return array(
			'basePath' => $this->basePath(),
			'dir' => $this->dir(),
			'dirPath' => $this->dirPath(),
			'file' => $this->_file,
			'filePath' => $this->_filePath,
			'parentDir' => $this->_parentDir,
			'folders' => (is_array($contents)) ? $contents[0] : $contents,
			'files' => (is_array($contents)) ? $contents[1] : $contents,
			'_args' => $this->_args,
			'actionMap' => $this->actionMap
		);
	}
	
	static public function encodePath($path) {
		if (!$path)
			return null;
			
		return base64_encode($path);
	}
	
	static public function decodePath($pathEncoded) {
		if (!$pathEncoded)
			return false;
			
		return base64_decode($pathEncoded,true);
	}
}

class FileManagerFolderNotFoundException extends CakeException {
	
	public function __construct($path) {
		
		$message = __("Folder '%s' does not exist",$path);
		parent::__construct($message);
	}
	
}


?>