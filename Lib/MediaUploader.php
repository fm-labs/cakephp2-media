<?php
App::uses('MediaTools','Media.Lib');

class MediaUploader {
	
	const UPLOAD_ERR_MIN_FILE_SIZE = 100;
	const UPLOAD_ERR_MAX_FILE_SIZE = 101;
	const UPLOAD_ERR_MIME_TYPE = 102;
	const UPLOAD_ERR_FILE_EXT = 103;
	const UPLOAD_ERR_FILE_EXISTS = 104;
	const UPLOAD_ERR_STORE_UPLOAD = 105;
	
	protected $_data;
	
	protected $_uploadDir = MEDIA_UPLOAD_DIR;
	
	protected $_minFileSize = 1;
	
	protected $_maxFileSize = 2000000;
	
	protected $_allowedMimeTypes = '*';
	
	protected $_allowedFileExtensions = '*';
	
	protected $_allowMultiple = false;
	
	protected $_filenamePattern = false;
	
	protected $_filenameSlug = "_";
	
	public function __construct($data = array()) {
		$this->setData($data);
	}
	
	public function setData($data) {
		$this->_data = $data;
	}
	
	public function setUploadDir($path) {
		if (!is_dir($path) || !is_writeable($path))
			throw new UploadException(UPLOAD_ERR_CANT_WRITE);
		
		$this->_uploadDir = $path;
	}
	
	/**
	 * Minimum upload file size in bytes
	 * 
	 * @param int $size File size in bytes
	 */
	public function setMinFileSize($size) {
		$this->_minFileSize = (int) $size;
	}

	/**
	 * Maximum upload file size in bytes
	 *
	 * @param int $size File size in bytes
	 */
	public function setMaxFileSize($size) {
		$this->_maxFileSize = (int) $size;
	}
	
	/**
	 * Allowed upload file mime type(s)
	 * 
	 * @param string|array $type
	 */
	public function setAllowedMimeType($type) {
		$this->_allowedMimeTypes = $type;
	}
	
	/**
	 * Allowed upload file extension(s)
	 * 
	 * @param string|array $ext
	 */
	public function setAllowedFileExtension($ext) {
		$this->_allowedFileExtensions = $ext;
	}
	
	/**
	 * Get current configuration
	 * 
	 * @return array
	 */
	public function getConfig() {
		
		return array(
			'uploadDir' => $this->_uploadDir,
			'multiple' => $this->_allowMultiple,
			'minFileSize' => $this->_minFileSize,
			'maxFileSize' => $this->_maxFileSize,
			'allowedMimeType' => $this->_allowedMimeTypes,
			'allowedFileExtension' => $this->_allowedFileExtensions,
			'slug' => $this->_filenameSlug,				
			'hashFilename' => false,
			'allowOverwrite' => false,
			// 'allowEmpty' => true,
		);
	}
	
	/**
	 * Perform upload
	 */
	public function upload() {
		$config = $this->getConfig();
		return $this->_upload($this->_data, $config);
	}
	
	/**
	 * Upload Handler
	 * 
	 * @param array $upload
	 * @param array $config
	 * @throws UploadException
	 */
	protected function _upload($upload, $config) {
	
		// validate upload error
		if ($upload['error'] > 0) {
			throw new UploadException($upload['error']);
		}
	
		// check upload dir
		if (!is_dir($config['uploadDir']) || !is_writeable($config['uploadDir'])) {
			//$upload['error'] = UPLOAD_ERR_CANT_WRITE;
			throw new UploadException(UPLOAD_ERR_CANT_WRITE);
		}
		
		// validate size
		if ($upload['size'] < $config['minFileSize'])
			throw new UploadException(self::UPLOAD_ERR_MIN_FILE_SIZE);
		elseif ($upload['size'] > $config['maxFileSize'])
			throw new UploadException(self::UPLOAD_ERR_MAX_FILE_SIZE);
	
		// validate mime
		elseif (!MediaTools::validateMimeType($upload['type'], $config['allowedMimeType']))
			throw new UploadException(self::UPLOAD_ERR_MIME_TYPE);
	
		// split basename
		list($filename,$ext, $dotExt) = MediaTools::splitBasename(trim($upload['name']));
	
		//validate extension
		if (!MediaTools::validateFileExtension($ext, $config['allowedFileExtension']))
			throw new UploadException(self::UPLOAD_ERR_FILE_EXT);
	
		// filename
		$filename = Inflector::slug($filename,$config['slug']);
		if ($config['hashFilename']) {
			$filename = sha1($filename);
		}
		$filename = uniqid($filename.'_');
		
		$basename = $filename.$dotExt;
		$path = $config['uploadDir'];
		
		//build targetname
		$target = $path . $basename;
		if (file_exists($target) && $config['allowOverwrite'] == false) {
			$i = 0;
			$_filename = $filename;
			do {
				$filename = $_filename.'_'.++$i;
				$basename = $filename.$dotExt;
				$target = $path.$basename;
			} while(file_exists($target) == true);
		}		
	
		debug("Uploading file to ".$target);
		
		//move uploaded file to tmp upload dir
		//TODO use a file engine here. Something like $this->_engine->storeTemporaryUpload($upload);
		if (is_uploaded_file($upload['tmp_name'])) {
			if (!move_uploaded_file($upload['tmp_name'], $target))
				throw new UploadException(self::UPLOAD_ERR_STORE_UPLOAD);
		}
		elseif (!copy($upload['tmp_name'], $target))
			throw new UploadException(self::UPLOAD_ERR_STORE_UPLOAD);
	
		return array(
			'name' => $upload['name'],
			'type' => $upload['type'],
			'size' => $upload['size'],
			'path' => $target,
			'basename' => $basename,
			'filename' => $filename,
			'ext' => $ext,
			'dotExt' => $dotExt,
		);
	}	
	
}

class UploadException extends CakeException {

	public function __construct($errCode, $upload = null, $code = 500) {
		$message = $this->_mapError($errCode, $upload);
		parent::__construct($message, $code);
	}

	protected function _mapError($errCode, $upload) {

		$errors = array(
			UPLOAD_ERR_OK => __("Upload successful"),
			UPLOAD_ERR_INI_SIZE => __("Maximum ini file size exceeded (%s)",ini_get('upload_max_filesize')),
			UPLOAD_ERR_FORM_SIZE => __("Maximum form file size exceeded"),
			UPLOAD_ERR_PARTIAL => __("File only partially uploaded"),
			UPLOAD_ERR_NO_FILE => __("No file uploaded"),
			UPLOAD_ERR_NO_TMP_DIR => __("Upload directory missing"), //PHP 5.0.3+
			UPLOAD_ERR_CANT_WRITE => __("Cant write to upload directory"), //PHP 5.1.0+
			UPLOAD_ERR_EXTENSION => __("Upload extension error"), //PHP 5.2.0+
			MediaUploader::UPLOAD_ERR_FILE_EXISTS => __('File already exists'),
			MediaUploader::UPLOAD_ERR_FILE_EXT => __('Invalid file extension'),
			MediaUploader::UPLOAD_ERR_MIME_TYPE => __('Invalid mime type'),
			MediaUploader::UPLOAD_ERR_MIN_FILE_SIZE => __('Minimum file size error'),
			MediaUploader::UPLOAD_ERR_MAX_FILE_SIZE => __("Maximum file size exceeded"),
			MediaUploader::UPLOAD_ERR_STORE_UPLOAD => __("Failed to store uploaded file"),
		);

		if (isset($errors[$errCode]))
			return $errors[$errCode];

		return __("Unknown upload error");
	}
}