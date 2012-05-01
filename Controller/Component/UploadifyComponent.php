<?php
App::uses('Folder','Utility');

class UploadifyComponent extends Component {
	
/**
 * Controller Instance
 * @var Controller
 */
	protected $Controller = null;

/**
 * $_FILES array index name
 * Usage: $_FILES[$this->uploadName]
 * 
 * @var string
 */	
	public $uploadName = "Filedata";
		
/**
 * Upload Path
 * @var string
 */
	public $uploadPath = TMP;
	
/**
 * Upload Dir
 * @var string
 */
	public $uploadDir = '/';
	

/**
 * Allowed FileExtension
 * @var mixed
 */	
	public $extensions = array();
	
/**
 * Return Type (json|html|text)
 * @var string
 */
	public $returnType = "json";
	
/**
 * Maximum Filesize in Bytes (max 2GB supported)
 * 
 * @var long
 */	
	public $fileSizeMax = 2147483647;

/**
 * Invalid Characters in FileName
 * 
 * @var string
 */	
	public $fileNameInvalidRegex = '\.\s\!\@\#\$\%\^\&\(\)\+\=\{\}\[\]\'\,\~\`\-';

/**
 * Invalid Characters Replacement Character
 * 
 * @var string
 */	
	public $fileNameInvalidSlug = '_';

/**
 * FileName Case Sensitive
 * If false, fileName will be converted to lower-case characters only
 * 
 * @var unknown_type
 */	
	public $fileNameCaseSensitive = true;
	
/**
 * Maxlength of FileName
 * 
 * @var int
 */	
	public $fileNameMaxLength = 255;

/**
 * UploadifyUpload Instance
 * @var UploadifyUpload
 */	
	public $Upload;
	
/**
 * Dispatch Error Flag
 * @var boolean
 */	
	private $_dispatchError = false;
	
	public function initialize(&$controller, $settings = array()) {
		$this->Controller =& $controller;
	}
	
	public function startup(&$controller) {
	}

/* 
 * Set / Get Response	
 */
	public function response($message = null, $status = UploadifyUpload::STATUS_ERROR) {
		if ($message === null)
			return $this->Upload->toArray();
			
		$this->Upload->message = $message;
		$this->Upload->status = $status;
		return $this;
	}
	
/* 
 * Set / Get UploadDir	
 */
	public function uploadDir($dir = null) {
		if ($dir === null)
			return $this->uploadDir;

		$_fullPath = $this->uploadPath().$dir;
		if (!is_dir($_fullPath))
			throw new UploadifyComponentException(__("Upload Directory %s does not exist in %s",$dir, $this->uploadPath));
		elseif(!is_writable($_fullPath))
			throw new UploadifyComponentException(__("Upload Directory %s in %s is not writeable",$dir, $this->uploadPath));
			
		$this->uploadDir = $dir;
			
		return $this;
	}
	
/* 
 * Set / Get UploadPath
 */
	public function uploadPath($path = null) {
		if ($path === null)
			return $this->uploadPath;
			
		$this->uploadPath = substr(Folder::slashTerm($path), 0, -1);
		return $this;
	}
	
/* 
 * Get FullUploadPath
 */
	public function uploadFullPath() {
		return Folder::slashTerm($this->uploadPath() . $this->uploadDir());
	}
	
/**
 * Dispatch Upload Method
 * @param string $method
 * @throws UploadifyComponentException
 */	
	private function _dispatch($method) {
		
		if ($this->_dispatchError === true)
			return false;

		try {
			if (!is_callable(array($this,$method))) {
				throw new UploadifyComponentException(__("Method UploadifyComponent::%s() does not exist",$method));
			} else {
				call_user_method($method, $this);
			}
		} catch(Exception $e) {
			$this->_dispatchError = true;
			$this->response($e->getMessage());	
		}
	}

/**
 * Perform Upload
 */	
	public function upload() {
		//reset
		$this->_dispatchError = false;
		$this->Upload = new UploadifyUpload();
		
		//validateSettings
		$this->uploadPath($this->uploadPath);
		$this->uploadDir($this->uploadDir);
		
		//parsePostData
		$this->_dispatch('parseParams');
		
		//parsePostData
		$this->_dispatch('parsePostData');
		
		//parseUploadFiles
		$this->_dispatch('parseUploadFiles');
		
		//processUploadFiles
		$this->_dispatch('processUploadFiles');
		
		//validateFileExtension
		
		//validateFileSize
		
		//validateFileName
		
	}

	protected function parseParams() {
		
		if (isset($this->Controller->passedArgs['dir'])) {
			try {
				$this->uploadDir(base64_decode($this->Controller->passedArgs['dir']));
			} catch(Exception $e) {
				CakeLog::write('error',$e->getMessage());
			}
		}
	}
	
	protected function parsePostData() {
		
		if (isset($_POST['dir'])) {
			$this->uploadDir($_POST['dir']);
		}
	}
	
/**
 * Parse Upload Files
 * @return boolean
 * @throw UploadifyComponentException
 */	
	protected function parseUploadFiles() {
		$uploadErrors = array(
	        0=>__("There is no error, the file uploaded successfully"),
	        1=>__("The uploaded file exceeds the upload_max_filesize directive in php.ini"),
	        2=>__("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"),
	        3=>__("The uploaded file was only partially uploaded"),
	        4=>__("No file was uploaded"),
	        6=>__("Missing a temporary folder")
		);
		
		// Validate the upload
		try {
			if (empty($_FILES)) {
				throw new UploadifyComponentException(__('No upload detected'));
			} elseif (!empty($_FILES) && !isset($_FILES[$this->uploadName])) {
				throw new UploadifyComponentException(__('Upload with invalid uploadName detected'));
			}elseif (!isset($_FILES[$this->uploadName])) {
				throw new UploadifyComponentException(sprintf(__("No upload found in \$_FILES for %s"),$this->uploadName));
			} elseif (isset($_FILES[$this->uploadName]["error"]) && $_FILES[$this->uploadName]["error"] != 0) {
				throw new UploadifyComponentException($uploadErrors[$_FILES[$this->uploadName]["error"]]);
			} elseif (!isset($_FILES[$this->uploadName]["tmp_name"]) || !@is_uploaded_file($_FILES[$this->uploadName]["tmp_name"])) {
				throw new UploadifyComponentException(__("Upload failed is_uploaded_file test."));
			} elseif (!isset($_FILES[$this->uploadName]['name'])) {
				throw new UploadifyComponentException(__("File has no name."));
			}
		} catch(Exception $e) {
			throw new UploadifyComponentException($e->getMessage());
			return false;
		}
		$this->Upload->uploadPath = $this->uploadPath;
		$this->Upload->uploadDir = $this->uploadDir;
		$tmpName = $_FILES[$this->uploadName]['tmp_name'];
		$this->Upload->tmpName = $tmpName;
		$uploadName = $_FILES[$this->uploadName]['name'];
		$uploadInfo = pathinfo($uploadName);
		
		
		// Validate the file size (Warning: the largest files supported by this code is 2GB)
		try {
			$fileSize = @filesize($_FILES[$this->uploadName]["tmp_name"]);
			if (!$fileSize) {
				$fileSize = $_FILES[$this->uploadName]["size"];
			}
			
			if (!$fileSize || $fileSize > $this->fileSizeMax) {
				throw new UploadifyComponentException(__("File exceeds the maximum allowed size"));
			}
	
			if ($fileSize <= 0) {
				throw new UploadifyComponentException(__("File size outside allowed lower bound"));
			}
		} catch(Exception $e) {
			throw new UploadifyComponentException($e->getMessage());
			return false;
		}
		$this->Upload->fileSize = $fileSize;
		
		
		//Validate Extension
		try {
			$ext = $uploadInfo['extension'];
			if (!empty($this->extension) && !in_array($ext,$this->extensions))
				throw new UploadifyComponentException(__("File extension %s is not allowed",$ext));
				
		} catch(Exception $e) {
			throw new UploadifyComponentException($e->getMessage());
			return false;
		}
		$this->Upload->fileExt = $ext;
		
		//Determine Mime Type
		$type = $_FILES[$this->uploadName]["type"];
		if ($this->Controller->response->getMimeType($this->Upload->fileExt))
			$type = $this->Controller->response->getMimeType($this->Upload->fileExt);
			
		$this->Upload->fileMime = $type;
		
		// Validate file name (for our purposes we'll just remove invalid characters)
		try {
			$fileName = $uploadInfo['filename'];
			$fileName = $this->_cleanFileName($fileName);
			$fileBasename = ($ext) ? $fileName.".".$ext : $fileName;
			
			if (strlen($fileName) == 0 || strlen($fileName) > $this->fileNameMaxLength)
				throw new UploadifyComponentException(__("Invalid file name (max %s chars)",$this->fileNameMaxLength) );
			
		} catch(Exception $e) {
			throw new UploadifyComponentException($e->getMessage());
			return false;
		}
		$this->Upload->fileName = $fileName;
		$this->Upload->fileBasename = $fileBasename;
	}
	
/**
 * Remove invalid characters from $fileName 
 * 
 * @param string $fileName FileName without(!) extension
 * @return string Cleaned $fileName
 */	
	private function _cleanFileName($fileName) {
		if (!$this->fileNameCaseSensitive) {
			$fileName = strtolower($fileName);
		}
		
		$pattern = '/['.$this->fileNameInvalidRegex.']/i';
		return preg_replace($pattern, $this->fileNameInvalidSlug, $fileName);
	}
	
	protected function processUploadFiles() {
		
		//filepath
		$targetFilePath = $this->uploadFullPath() . $this->Upload->fileBasename;
		$this->Upload->targetFilePath = $targetFilePath;
		
		if (file_exists($targetFilePath)) {
			throw new UploadifyComponentException(__("File %s already exists in %s",$this->Upload->fileBasename, $this->uploadDir()));
			return false;
		}
		
		if (!move_uploaded_file($this->Upload->tmpName, $targetFilePath)) {
			throw new UploadifyComponentException(__("Failed to move file %s to %s",$this->Upload->fileBasename, $this->uploadDir()));
			return false;
		}
		
		$this->response(__("Upload Successful"),UploadifyUpload::STATUS_SUCCESS);
	}
	
	
	
	public function respond() {

		$this->Controller->layout = "empty";
		$this->Controller->viewClass = "Media.Json";
		$this->Controller->set('uploadResponse',$this->Upload->toArray());
		$this->Controller->set('json','uploadResponse');
		
	}
	
}

class UploadifyUpload {
	
	const STATUS_SUCCESS = "SUCCESS";
	const STATUS_ERROR = "ERROR";
	
	public $status = "UNKNOWN";
	
	public $message;
	
	public $uploadPath;
	
	public $uploadDir;
	
	public $tmpName;
	
	public $fileBasename;
	
	public $fileName;
	
	public $fileExt;
	
	public $fileSize = 0;
	
	public $fileMime;
	
	public $targetFilePath;
	
	public $outputVars = array('status','message','uploadDir','filePath','fileName',
		'fileExt','fileBasename','fileSize','fileMime','tmpName','targetFilePath');
	
	public function toArray() {
		
		$array = array();
		$classVars = get_class_vars(get_class($this));
		foreach($this->outputVars as $var) {
			if (!array_key_exists($var,$classVars))
				continue;
				
			$array[$var] = $this->$var;
		}
		return $array;
	}
	
}

class UploadifyComponentException extends CakeException {}

?>