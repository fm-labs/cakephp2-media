<?php
App::uses('Folder','Utility');

if (!defined('STORAGE_ROOT')) {
	define('STORAGE_ROOT',TMP.DS."uploads".DS);
	#debug(__("STORAGE ROOT IS NOT DEFINED. USING DEFAULT: '%s'",STORAGE_ROOT));
}

class UploadifyComponent extends Component {
	
	private $_controller;
	
	public $Storage = null;

/**
 * Enable/disable Debugging
 */	
	public $debug = false;
	
/**
 * $_FILES array index name
 * Usage: $_FILES[$this->uploadName]
 * 
 * @var string
 */	
	public $uploadName = "Filedata";

/**
 * Root path with trailing directory separator
 * e.g. /var/files/uploads/
 * 
 * @var string
 */	
	public $storageRoot = STORAGE_ROOT;

/**
 * TargetFolder relative to the root path with tailing directory separator
 * e.g. documents/ or documents/subfolder1/subfolder2/
 * 
 * @var string
 */	
	public $storageFolder = "";

/**
 * Extensions Whitelist
 * 
 * @var mixed
 */	
	public $extensions = array('jpg','jpeg','gif','png');

/**
 * Enable/Disable Upload
 * 
 * @var boolean
 */	
	public $enabled = true;
	
/**
 * Enable/Disable Overwritting - SUPPORT DISABLED, because we use force unique filename anyways
 * 
 * @var boolean
 */	
	public $overwrite = true;

/**
 * Filename Hashing
 * The file will be stored with the filehash as filename
 * 
 * @var boolean
 */	
	public $hashFileName = true;

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
 * Store Upload
 * 
 * @var boolean
 */	
	public $storeUpload = true;
	
/**
 * ResponseType
 * Choose between text/json/html
 * 
 * @var string
 */	
	public $responseType = "text";


/**
 * List of allowed keys which will be fetched from $_POST into data array
 * Usage: array('id','field2','field3) or 'field1' or '*' to accept all data or array()/null to deny all
 * Default: deny all
 * 
 * @var mixed
 */	
	public $dataWhitelist = array();	

	
/** 
 * List of REQUIERED post vars
 * Make sure the post var also passes the dataWhitelist restrictions
 * Default: none
 * 
 * @var mixed
 * @todo Automatically add $dataRequired fields to $dataWhitelist
 */	
	public $dataRequired = array();
	
/**
 * Response
 *	'STATUS'			=> 'UNKNOWN', //e.g."UPLOAD_ERROR"
 *	'MESSAGE'			=> 'UNKNOWN', //e.g."Upload was successfull"
 *	'FILE_PATH'			=> null, //e.g. "/var/www/uploads/documents/subfolder1/
 *	'FILE_NAME'			=> null, //e.g. "my_uploaded_document.doc" or "0393982098230fs90238fs0df8.doc"
 *	'DISPLAY_NAME' 		=> null, //e.g. my_uploaded_document
 *
 * @var mixed
 */	
	private $_response = array();

	private $_targetPath = "";

	private $__errors = array();
/**
 * Initailize Component and set default response
 * 
 * @param Controller $controller
 */	
	public function initialize(&$controller) {
		$this->_controller =& $controller;
		$this->_controller->layout = "empty";
		
		$this->Storage = new UploadifyStorage();
		
		$this->setResponse(null,__("Initialize Upload"));

	}

/**
 * Required Component callback
 * 
 * @param Controller $controller
 */	
	public function startup(&$controller) { 
		//Enable / disable debugging
		if ($this->debug == true)
			Configure::write('debug',2);
		else
			Configure::write('debug',0);

	}	
	
/**
 * Set Response
 * 
 * @param mixed $status
 * @param string $message
 */	
	public function setResponse($status,$message=null,$reset = false) {
		if (is_array($status)) {
			$response = $status;
		} else {
			$response = array(
				'STATUS' => $status,
				'MESSAGE' => $message
			);
		}
		if ($reset)
			$this->_response = $response;
		else
			$this->_response = Set::merge($this->_response,$response);
			
		return $this->_response;
	}
	
	public function getResponse() {
		return $this->_response;
	}

/**
 * Set ResponseStatus
 * Wrapper for CakeResponse->statusCode()
 * 
 * @param int $status
 */	
	public function setResponseStatus($status = 200) {
		$this->_controller->response->statusCode($status);
	}		

	
/**
 * Upload File
 * 
 * @return void
 */	
	public function upload() {
		
		
		$this->_dispatchMethod('_detectData');
		$this->_dispatchMethod('_checkData');
		$this->_dispatchMethod('_checkUpload');
		$this->_dispatchMethod('_checkUploadErrors');
		$this->_dispatchMethod('_checkEnabled');
		#$this->_dispatchMethod('_checkContext');
		#$this->_dispatchMethod('_checkPath');
		#$this->_dispatchMethod('_checkPostMaxSize');
		#$this->_dispatchMethod('_checkFileSize');
		#$this->_dispatchMethod('_checkExtensions');
		#$this->_dispatchMethod('_checkFileName');
		#$this->_dispatchMethod('_moveUpload');
		#$this->_dispatchMethod('_afterUpload');
		#$this->_dispatchMethod('_storeUpload');
	}



	
/**
 * Detect data from $_POST
 * 
 * @return boolean
 */	
	private function _detectData() {
		if ($this->_controller->request->is('post') && !empty($_POST) ) {

			//allow all post vars
			$_whitelist = $this->dataWhitelist;
			if (is_string($_whitelist) && $_whitelist == "*")
				$_whitelist = array_keys($_POST);
			//convert single strings to whitelist array
			elseif(is_string($_whitelist))
				$_whitelist = array($_whitelist);
			//prevent bugs with in_array() operations
			elseif (empty($_whitelist))
				$_whitelist = array();

			//add dataRequired entries to dataWhitelist
			$_whitelist = array_unique(array_merge($_whitelist,$this->dataRequired));
				
			//fetch post vars
			foreach($_POST as $key => $val):
				//only fetch post vars in the dataWhitelist
				if (in_array($key,$_whitelist)) {
					$this->Storage->$key = $val;
				} 
				//Ignore post vars sent by uploader
				elseif (in_array($key,array('Filename','Upload'))) {
					continue;
				}
				//Log invalid sent key as warning (security logging) 
				else {
					$this->warning(__("UploadifyComponent: Invalid key '%s' sent with value '%s'",
						h($key),h($val)
					));
				}
			endforeach;
			return true;
		}
		return false;
	}


/**
 * Detect Upload from $_FILES
 * 
 * @return boolean
 */	
	private function _checkUpload() {
		if (empty($_FILES)) {
			$this->setResponse('UPLOAD_ERROR',__('No upload detected'));
			return false;
		} elseif (!empty($_FILES) && !isset($_FILES[$this->uploadName])) {
			$this->setResponse('UPLOAD_ERROR',__('Upload with invalid uploadName detected'));
			return false;
		}
		return true;
	}

/**
 * Check Data
 * Performs check on current data set and triggers 'checkUploadData'-Callback (AFTER checking the required fields)
 * Callback usage:
 * $this->_controller->checkUploadData($this,$this->_data);
 * 
 * @return boolean
 */	
	private function _checkData() {
		//check required fields
		foreach ($this->dataRequired as $requiredField) {
			if (!$this->Storage->{$requiredField}) {
				$this->setResponse('CHECK_ERROR',__("Required field '%s' has not been submitted",$requiredField));
				return false;
			}
		}
		
		//checkUploadData callback
		if (method_exists($this->_controller,'checkUploadData')) {
			if (!$this->_controller->checkUploadData($this->Storage->toArray())) {
				$this->setResponse('CHECK_ERROR',__('Data check failed'));
				return false;
			}
		}		
		
		return true;
	}
	
	
/**
 * Check Upload Enabled
 * 
 * @return boolean
 */		
	private function _checkEnabled() {
		// Check if upload is enabled (disabled by default)
		if (!$this->enabled) {
			$this->setResponse('UPLOAD_ERROR',__('Sorry. The storage is currently not available.'));
			return false;	
		}
		return true;
	}

/**
 * Check PostMaxSize
 * 
 * @return boolean
 */		
	private function _checkPostMaxSize() {
		// Check post_max_size (http://us3.php.net/manual/en/features.file-upload.php#73762)
		$POST_MAX_SIZE = ini_get('post_max_size');
		$unit = strtoupper(substr($POST_MAX_SIZE, -1));
		$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));
	
		if (isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
			#header("HTTP/1.1 500 Internal Server Error"); // This will trigger an uploadError event in SWFUpload
			$this->setResponse("POST_MAX_SIZE_EXCEEDED", "POST exceeded maximum allowed size: ".$POST_MAX_SIZE);
			return false;
		} elseif (!isset($_SERVER['CONTENT_LENGTH'])) {
			$this->warning(__("UploadifyComponent: \$_SERVER['CONTENT_LENGTH'] not set in Http headers"));
		}
		return true;
	}

/**
 * Check UploadErrors
 * 
 * @return boolean
 */	
	private function _checkUploadErrors() {
		$uploadErrors = array(
	        0=>__("There is no error, the file uploaded successfully"),
	        1=>__("The uploaded file exceeds the upload_max_filesize directive in php.ini"),
	        2=>__("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"),
	        3=>__("The uploaded file was only partially uploaded"),
	        4=>__("No file was uploaded"),
	        6=>__("Missing a temporary folder")
		);
		// Validate the upload
		
		if (!isset($_FILES[$this->uploadName])) {
			$this->setResponse("UPLOAD_ERROR", sprintf(__("No upload found in \$_FILES for %s"),$this->uploadName));
			return false;
		} elseif (isset($_FILES[$this->uploadName]["error"]) && $_FILES[$this->uploadName]["error"] != 0) {
			$this->setResponse("UPLOAD_ERROR",$uploadErrors[$_FILES[$this->uploadName]["error"]]);
			return false;
		} elseif (!isset($_FILES[$this->uploadName]["tmp_name"]) || !@is_uploaded_file($_FILES[$this->uploadName]["tmp_name"])) {
			$this->setResponse("UPLOAD_ERROR",__("Upload failed is_uploaded_file test."));
			return false;
		} elseif (!isset($_FILES[$this->uploadName]['name'])) {
			$this->setResponse("UPLOAD_ERROR",__("File has no name."));
			return false;
		}		
		return true;
	}

/**
 * Check Extension
 * 
 * @return boolean
 */		
	private function _checkExtensions() {
		$uploadInfo = pathinfo($_FILES[$this->uploadName]['name']);
		$this->Storage->ext = @$uploadInfo['extension'];
		
		if (is_string($this->extensions) && $this->extensions == "*") {
			return true;
		}
		elseif (is_string($this->extensions)) {
			switch($this->extensions):
			case "images":
				$this->extensions = array('png','jpeg','jpg','gif');
				break;
			case "documents":
				$this->extensions = array('doc','docx','xls','xlsx','txt','rtf');
				break;
			case "audio":
				$this->extensions = array('mp3','wav');
				break;
			case "video":
				$this->extensions = array('avi','mp4');
				break;
			default:
				$this->extensions = array($this->extensions);
				break;
			endswitch;
		}
		
		if (!in_array($this->Storage->ext,$this->extensions)) {
			$this->setResponse('UPLOAD_ERROR',__("Invalid file type %s",$this->Storage->ext));
			return false;
		}
		
		return true;
	}
	
/**
 * Check FileSize
 * 
 * @return boolean
 */	
	private function _checkFileSize() {
		// Validate the file size (Warning: the largest files supported by this code is 2GB)
		$file_size = @filesize($_FILES[$this->uploadName]["tmp_name"]);
		if (!$file_size) {
			$file_size = $_FILES[$this->uploadName]["size"];
		}
		$this->Storage->size = $file_size;
		if (!$file_size || $file_size > $this->fileSizeMax) {
			$this->setResponse("UPLOAD_ERROR",__("File exceeds the maximum allowed size"));
			return false;
		}

		if ($file_size <= 0) {
			$this->setResponse("UPLOAD_ERROR",__("File size outside allowed lower bound"));
			return false;
		}		
		return true;
	}

	private function _checkContext() {
		return true;
		
		if (!$this->Storage->context()) {
			$this->setResponse("UPLOAD_ERROR",__("Invalid Context"));
			return false;
		}
		return true;
	}	
	
/**
 * Check Path
 * 
 * @return boolean
 */	
	private function _checkPath() {
		
		#$this->storageRoot = $this->Storage->getStorageRoot();
		#$this->storageFolder = $this->Storage->getMappedPath(false);
		
		#$this->_targetPath = $this->storageRoot . $this->storageFolder;
		$this->_targetPath = $this->Storage->getMappedPath(true);
		
		$Folder = new Folder($this->_targetPath,true);
		
		if (!is_dir($this->_targetPath) || !is_writeable($this->_targetPath)) {
			$this->error(__("UploadifyComponent: Upload directory '%s' does not exist or ist not writeable",$this->_targetPath));
			if (Configure::read('debug')>0){
				$this->setResponse("UPLOAD_ERROR", __("Upload directory '%s' is currently not accessable",$this->_targetPath));
			} else {
				$this->setResponse("UPLOAD_ERROR", __("Upload directory is currently not accessable"));
			}
			return false;
		}			
		return true;
	}
	

/**
 * Check FileName
 * 
 * @return boolean
 */	
	private function _checkFileName() {
		// Validate file name (for our purposes we'll just remove invalid characters)
		$uploadInfo = pathinfo($_FILES[$this->uploadName]['name']);
		$base_name = $uploadInfo['filename'];
		$file_name = $display_name = $this->__cleanFileName($base_name);
		if (strlen($file_name) == 0 || strlen($file_name) > $this->fileNameMaxLength) {
			$this->setResponse("UPLOAD_ERROR",__("Invalid file name (max %s chars)",$this->fileNameMaxLength) );
			return false;
		}
		
		//Generate FileHash
		$this->Storage->filehash = sha1($display_name.$this->Storage->size.hash_file('sha1',$_FILES[$this->uploadName]["tmp_name"]));
		
		if ($this->hashFileName == true) {
			$file_name = $this->Storage->filehash;
		}
		$_targetFilePath = $this->_targetPath.$file_name.".".$this->Storage->ext;
		
		//Overwriting
		/*
		if (file_exists($_targetFilePath) && $this->overwrite == false) {
			$this->setResponse("UPLOAD_ERROR",__("Overwriting not permitted"));
			return false;
		}
		*/		
		$this->Storage->name = $this->__uniqueFilename($_targetFilePath);
		$this->Storage->display_name = $display_name;
		
		return true;
	}

/**
 * Remove invalid characters from $fileName 
 * 
 * @param string $fileName FileName without(!) extension
 * @return string Cleaned $fileName
 */	
	private function __cleanFileName($fileName) {
		if (!$this->fileNameCaseSensitive) {
			$fileName = strtolower($fileName);
		}
		
		$pattern = '/['.$this->fileNameInvalidRegex.']/i';
		return preg_replace($pattern, $this->fileNameInvalidSlug, $fileName);
	}

/**
 * Move uploaded file to target directory
 * 
 * @return boolean
 */	
	private function _moveUpload() {
		
		$_targetFilePath = $this->_targetPath.$this->Storage->name.".".$this->Storage->ext;
		$this->log(__("UploadifyComponent: Storing file %s",$_targetFilePath));
		if(!move_uploaded_file($_FILES[$this->uploadName]['tmp_name'],$_targetFilePath)) {
			$this->error(__("UploadifyComponent: Failed to move uploaded file from %s to %s",
				$_FILES[$this->uploadName]['tmp_name'],$_targetFilePath
			));
			$this->setResponse('UPLOAD_ERROR',__("Could not finish upload"));
			return false;
		}
		$this->log(__("UploadifyComponent: File %s successfully uploaded to %s",$this->Storage->name.".".$this->Storage->ext,$this->_targetPath));
		$this->setResponse('SUCCESS',__("Upload successfull"));
		return true;
	}

/**
 * After Upload Callback
 */	
	private function _afterUpload() {
		//afterUpload callback
		if (method_exists($this->_controller,'afterUpload')) {
			return $this->_controller->afterUpload($this);
		}
		return true;
	}	
	
/**
 * Store Upload callback
 * Data array with internal vars and custom post vars will be handed over to the callback function in the Controller
 * Internal vars:
 * * id
 * * operation
 * * context
 * * path
 * * name
 * * display_name
 * * ext
 * * size
 * * filehash
 * 
 * @return boolean
 */	
	private function _storeUpload() {

		if (!$this->storeUpload)
			return true;
		
		//storeUpload callback
		if (method_exists($this->_controller,'storeUpload')) {
			if (!$this->_controller->storeUpload($this->Storage)) {
				$this->setResponse('UPLOAD_ERROR',__('Could not store Upload'));
				return false;
			}
		} else {
			if (!$this->Storage->storeUpload()) {
				if (Configure::read('debug')>0) {
					$this->setResponse('UPLOAD_ERROR',__('Storring upload failed'));
				} else {
					$this->setResponse('UPLOAD_ERROR',__('Could not store Upload'));
				}
				return false;
			}
		}
	}

/**
 * Execute upload methods. Won't continue if an error occured previously
 * 
 * @param string $method
 */	
	private function _dispatchMethod($method) {
		if ($this->error()) {
			return false;
		}
		
		if (!call_user_method($method,$this)) {
			$this->error(__("UploadifyComponent: Could not complete task '%s' successfully",$method));
		}
	}

/**
 * Creates unique filename in given path
 * 
 * @param string $filePath Full path to file incl. filename and filext
 * @todo Move 'uniqueFilename' functionality in library
 */	
	private function __uniqueFilename($filePath) {
		return basename($filePath);
		#return FileUtil::uniqueFilename($filePath,null,'filename');
	}	
	
	
/**
 * Check
 */	
	public function check() {
		/*
		Uploadify v3.0.0
		Copyright (c) 2010 Ronnie Garcia
		
		Return true if the file exists
		*/
		$pathinfo = pathinfo($_POST['filename']);
		$checkFilePath = STORAGE_ROOT . $this->storageFolder . $this->__cleanFileName($pathinfo['filename']).".".$pathinfo['extension'];
		if (file_exists($checkFilePath)) {
			$response = 1;
		} else {
			$response = 0;
		}
		
		$this->responseType = "text";
		$this->setResponse('CHECK_SUCCESS',$response);
	}	

/**
 * Required Component callback
 * 
 * @param Controller $controller
 */	
	public function shutdown(&$controller) { }	
	
	
/**
 * Rebuild response with all values before rendering
 * @param Controller $controller
 */	
	public function beforeRender(&$controller) {
		$this->_render();
	}

/**
 * Prepare response data to be displayed speciafied in self::$responseType
 * 
 * @todo: Add Uploadify ViewClass
 * @return void
 */	
	private function _render() {
		
		$this->setResponse(Set::reverse($this->Storage));
		if (Configure::read('debug') > 0) {
			//set extra vars for debugging
		}

		switch ($this->responseType) {
			case "json":
				$response = json_encode($this->Storage);
				break;
			case "html":
				$response = "<ul>\n";
				foreach ($this->_response as $key => $val):
					$response .= sprintf("<li>%s:%s</li>\n",$key,$val);
				endforeach;
				$response = "</ul>\n";
				break;
			case "php_var":
				$response = Debugger::exportVar($this->_response);
				break;
			case "text":
			default:
				$response = $this->_response['MESSAGE'];
				break;
		}
		
		$this->_controller->set(compact('response'));
	}
	
	public function beforeRedirect($controller, $url, $status = null, $exit = true) {
		return $url;
	}

	public function warning() {}
	
	public function error($msg = null) {
		if (is_null($msg))
			return $this->__errors;
			
		$this->__errors[] = $msg;
	}
	
	
	
}

class UploadifyStorage {}