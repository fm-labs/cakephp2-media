<?php
App::uses('Folder','Utility');
App::uses('File','Utility');

class FileExplorer {
	
	public $autoLoadContents = true;
	
	/**
	 * Root path WITH trailing DS
	 * 
	 * @var string
	 */
	protected $basePath;

	protected $excludes = array();
	
	/**
	 * Current directory. 
	 * Relative from root path. WITH trailing DS
	 * 
	 * @var string
	 */
	protected $_dir = '/';
	
	
	protected $_contents = array();
	
	/**
	 * Folder instance
	 * 
	 * @var Folder
	 */
	protected $_Folder;
	
	public function __construct($config = array()) {
		
		if (is_string($config)) {
			$config = Configure::read('Media.FileExplorer.'.$config);
		}
		
		if (!is_array($config))
			throw new Exception('Invalid config');
		
		$this->_set($config);

		if (!is_dir($this->basePath))
			throw new Exception(__('Root path %s does not exist',$this->basePath));
	}
	
	protected function _set($config = array()) {
		$classVars = get_class_vars(get_class($this));
		
		foreach($config as $k => $v) {
			if (!preg_match('/^_/',$k) && array_key_exists($k, $classVars)) {
				$this->{$k} = $v;
			}	
		}
	}
	
	public function exclude($exceptions = null) {
		if ($exceptions === null)
			return $this->excludes;
		
		$this->excludes = $exceptions;
		return $this;
	}
	
	/**
	 * Directory Setter/Getter
	 * 
	 * @param string $dir
	 * @throws Exception
	 * @return string|FileExplorer
	 */
	public function dir($dir = null) {
		if ($dir === null) {
			return $this->_dir;
		}
		
		if (!is_string($dir))
			throw new Exception('Invalid Argument');
		
		//sanitize dir input
		$dir = self::cleanDirname($dir);
		$dir = self::slashTerm($dir);
		
		if (Folder::isAbsolute($dir)) {
			if (Folder::isWindowsPath($dir))
				throw new Exception(__('Cannot use absolute windows path as directory'));
		} else {
			$dir = $this->_dir . $dir;
		}
		
		$this->_dir = $dir;
		
		//TODO check if path has changed
		$this->_Folder = $this->getFolder();
		if (!$this->_Folder->pwd()) {
			throw new Exception(__("Folder %s not found",$dir));
		}
		
		// reset contents
		$this->_contents = null;
		if ($this->autoLoadContents)
			$this->readContents();
		
		return $this;
	}

	/**
	 * Create a new directory
	 * 
	 * @param string $dirname
	 * @param string $mode
	 * @throws Exception
	 * @return FileExplorer
	 */
	public function create($dirname, $mode = 0755) {
		
		// TODO input sanitation
		$_dirname = basename($dirname);
		if ($dirname != $_dirname) {
			throw new Exception("$dirname is an invalid argument");
		}
		$dirpath = $this->getDirPath(null,true).$_dirname;
		if (file_exists($dirpath) || is_dir($dirpath)) {
			throw new Exception("Directory $_dirname already exists");
		}
		
		$Folder = new Folder($dirpath, true);
		if (!is_dir($dirpath))
			throw new Exception("Failed to create folder $dirpath. Check your permissions.");
		
		return $this;
	}
	
	public function move($dir = null, $target) {
		if ($dir === null)
			$dir = $this->_dir;
		
		if (self::isRoot($dir))
			throw new Exception('Cannot move root dir');
		
		if (!$target)
			throw new Exception('No target given');
		
		if (!self::isAbsolute($target)) {
			$target = $this->getParentDir().$target;
		}
		
		if (self::inPath($dir,$target))
			throw new Exception('Cannot move dir to a subdirectory');
		
		$_target = $this->getDirPath($target,true);
		if (self::exists($_target))
			throw new Exception('Target already exists');
		
		$Folder = $this->getFolder($dir);
		
		if (!$Folder->move(array(
			'to'=>$_target
		))) {
			throw new Exception("Failed to move to $target");
		}
		
		$this->dir(self::parentDir($target));
		
		return $this;
	}
	
	public function add($file, $mode = 0755) {

		if (!self::isAbsolute($file)) {
			$file = $this->getDirPath().$file;
		}
		
		$filepath = $this->getDirPath(dirname($file),true).basename($file);
		if (file_exists($filepath)) {
			//throw new Exception("File $file already exists");
			$pathinfo = pathinfo($filepath);
			$ext = (@$pathinfo['extension']) ? '.'.$pathinfo['extension'] : '';
			$i = 1;
			do {
				$_filepath = $pathinfo['dirname'].DS.sprintf("%s_%s%s",$pathinfo['filename'],$i++,$ext);
			} while(file_exists($_filepath));
			$filepath = $_filepath;
		}
	
		$File = new File($filepath, true);
		if (!is_file($filepath))
			throw new Exception("Failed to create file $filepath. Check your permissions.");
	
		return $this;
	}	
	
	public function delete($file) {
		
		if (!self::isAbsolute($file)) {
			$file = $this->getDirPath().$file;
		}
		
		$filepath = $this->getDirPath(null,true).basename($file);
		if (!file_exists($filepath))
			throw new Exception(__('File %s not found',$file));
		
		if (!is_writeable($filepath))
			throw new Exception(__('No permission to delete %s',$file));
		
		if (!unlink($filepath))
			throw new Exception(__('Failed to delete file %s',$file));
		
		$this->readContents();
		
		return $this;
	}
	
	protected function &getFolder($dir = null) {
		if ($dir === null)
			$dir = $this->_dir;
		
		$dirpath = $this->getDirPath($dir,true);
		$Folder = new Folder($dirpath,false);
		return $Folder;
	}
	
	/**
	 * Check if file or folder is writeable
	 * 
	 * @param string $dir
	 * @return boolean
	 */
	public function isWriteable($dir = null) {
		if ($dir === null)
			$dir = $this->_dir;
		
		return is_writable($this->getDirPath($dir,true));
	}
	
	/**
	 * Returns FileExplorerFile instance
	 * 
	 * @param string $file Filename or filepath. If path is given a directory change occurs!
	 * @param bool $create
	 * @throws Exception
	 * @return FileExplorerFile
	 */
	public function file($file = null, $create = false) {
		
		if (!$file) {
			throw new Exception(__('No file selected'));
		}
		
		$basename = basename($file);
		if ($basename != $file) {
			$dir = substr($file,0,strlen($basename)*-1);
			$this->dir($dir);
		}
		$filepath = $this->getFilePath($file, true);
		$File = new FileExplorerFile($this, $filepath, $create);
		
		return $File;
	}
	
	/**
	 * Get basename of parent directory
	 * 
	 * @return string
	 */
	public function getParentDir() {
		return self::parentDir($this->_dir);
	}
	
	/**
	 * Get path of directory
	 * 
	 * @param string $dir
	 * @param boolean $full
	 * @return string
	 */
	public function getDirPath($dir = null, $full = false) {
		if ($dir === null)
			$dir = $this->_dir;
		
		// check if path is relative
		if (!self::isAbsolute($dir))
			$dir = $this->_dir.$dir;
		
		$dir = self::slashTerm($dir);
		
		if ($full)
			return $this->basePath . substr($dir,1);
		
		return $dir;
	}
	
	/**
	 * Read contents of currently selected directory
	 * 
	 * @return FileExplorer
	 */
	public function readContents() {
		
		$exceptions = $this->excludes;
		
		if (!$this->_Folder || !$this->_Folder->pwd())
			return $this;
		
		list($folders,$files) = $this->_Folder->read(true,$exceptions,true);
		
		$_folders = array();
		foreach($folders as $folder) {
			array_push($_folders, array(
				'name' => basename($folder),
				'size' => null,
				'permissions' => null,
				'writeable' => is_writeable($folder)
			));
		}
		
		$_files = array();
		foreach($files as $file) {
			$pathinfo = pathinfo($file);
			array_push($_files, array(
				'name' => $pathinfo['filename'],
				'basename' => $pathinfo['basename'],
				'size' => filesize($file),
				'ext' => @$pathinfo['extension'],
				'permissions' => fileperms($file),
				'writeable' => is_writeable($file),
			));
		}
		
		$this->_contents = array('Folder'=>$_folders,'File'=>$_files);
		return $this;
	}
	
	/**
	 * Returns 
	 * @return multitype:
	 */
	public function getContents() {
		return $this->_contents;
	}
	
	public function toArray() {
		
		return array(
			'dir' => $this->dir(),
			'parent_dir' => $this->getParentDir(),
			'writeable' => $this->isWriteable(),
			'contents' => $this->getContents()
		);
	}
	
	public static function cleanDirname($dir) {

		$dir = trim($dir);
		$dir = preg_replace('/[\/\/]+/', '/', $dir); // "//" -> "/"

		// real path
		$parts = explode('/',$dir);
		$newparts = array();
		$offset = 0;
		for($i=0;$i<count($parts);$i++) {
			$part = $parts[$i];
			if ($part == '.') {
				continue;
			} elseif ($part == '..' && count($newparts) > 1) {
				#debug('remove idx: '.count($newparts)-1+$offset);
				#debug('remove '.$newparts[count($newparts)-1+$offset]);
				#debug($newparts);
				unset($newparts[count($newparts)-1+$offset]);
				$offset++;
			} elseif ($part == '..') {
				// can not go above root
			} else {
				$newparts[] = $part;
			}
		}
		$dir = join('/', $newparts);
		if (!$dir)
			$dir = '/';
		return $dir;
	}
	
	public static function parentDir($dir) {
		
		$dir = self::cleanDirname($dir);
		
		$parts = explode('/',$dir);
		
		$slice = 1;
		if ($parts[count($parts)-1] == '') {
			$slice = 2;
		}
		$parts = array_slice($parts, 0, -$slice);
		$parts[] = '';
		$parentDir = join('/',$parts);
		
		if (!$parentDir  || $parentDir == '/')
			return '/';
		
		return $parentDir;
	}
	
	/**
	 * Check if a directory path is absolute
	 * 
	 * @param string $dir FileExplorer directory path
	 * @return boolean
	 */
	public static function isAbsolute($dir) {
		
		if ($dir == '/' || $dir[0] == '/')
			return true;
		
		return false;
	}
	
	public static function exists($dir) {
		return file_exists($dir);
	}
	
	/**
	 * Checks if path2 is in path
	 * 
	 * @param string $path
	 * @param string $path2
	 * @return number
	 */
	public static function inPath($path, $path2) {
		$path = self::slashTerm($path);
		$path2 = self::slashTerm($path2);
		
		return (bool) preg_match('/^' . preg_quote($path, '/') . '(.*)/', $path2);
	}
	
	/**
	 * Check if directory path is root dir
	 * 
	 * @param string $dir
	 * @return boolean
	 */
	public static function isRoot($dir) {
		$dir = self::slashTerm($dir);
		if ($dir == '/')
			return true;
		
		return false;
	}
	
	/**
	 * Check if path has a trailing /
	 * 
	 * @param string $dir FileExplorer directory path
	 * @return boolean
	 */
	public static function isSlashTerm($dir) {
		if (!empty($dir) && ($dir == '/' || $dir[strlen($dir)-1] == '/'))
			return true;
		
		return false;
	}
	
	/**
	 * Adds a trailing / at the end of the path
	 * 
	 * @param string $dir FileExplorer directory path
	 * @return string
	 */
	public static function slashTerm($dir) {
		if (!self::isSlashTerm($dir)) {
			$dir .= '/';
		}
		return $dir;
	}
}

class FileExplorerFile {
	
	/**
	 * File instance
	 * 
	 * @var File
	 */
	protected $_File;
	
	/**
	 * FileExplorer instance reference
	 * 
	 * @var FileExplorer
	 */
	protected $_FileExplorer;
	
	protected $_dir;
	
	public function __construct(FileExplorer &$x, $filepath, $create = false, $mode = 0755) {
		
		$this->_dir = $x->dir();
		$this->_File = new File($filepath,$create,$mode);
		
	}
	
	public function __call($name, $args) {
		
		debug($args);
		if (method_exists($this->_File, $name) && is_callable(array($this->_File, $name))) {
			//call_user_func_array(array($this->_File, $name), func_get_args());
		}
		
		throw new Exception(__('Uknown method %s',$name));
	}
	
	public function toArray() {
		
		$File =& $this->_File;
		return array(
			'content' => $File->read(),
			'size' => $File->size(),
			'name' => $File->name(),
			'ext' => $File->ext(),
			'pwd' => $File->pwd(),
			'basename' => basename($File->pwd()),
			'writable' => $File->writable(),
			'dir' => $this->_dir
		);
	}
}