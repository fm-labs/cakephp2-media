<?php
App::uses('MediaUtil', 'Media.Lib');

class MediaUploader {

	const UPLOAD_ERR_MIN_FILE_SIZE = 100;
	const UPLOAD_ERR_MAX_FILE_SIZE = 101;
	const UPLOAD_ERR_MIME_TYPE = 102;
	const UPLOAD_ERR_FILE_EXT = 103;
	const UPLOAD_ERR_FILE_EXISTS = 104;
	const UPLOAD_ERR_STORE_UPLOAD = 105;

	protected $_config = array();

	protected $_data;

/**
 * Constructor
 *
 * @param array $config
 * @param null  $data
 */
	public function __construct($data = null, $config = array()) {
		// Setup default config
		$defaultConfig = array(
			'uploadDir' => TMP . 'uploads' . DS,
			'minFileSize' => 1,
			'maxFileSize' => 2 * 1024 * 1024, // 2MB
			'allowedMimeTypes' => '*',
			'allowedFileExtensions' => '*',
			'allowMultiple' => false,
			'filenamePattern' => false, // @todo Implement me
			'filenameSlug' => "_",
			'hashFilename' => false,
			'uniqueFilename' => false,
			'allowOverwrite' => true,
			'filename' => null, // filename override
		);
		$this->set($defaultConfig);

		// Apply config
		if (is_string($config)) {
			$config = Configure::read('Media.Upload.' . $config);
		}
		$this->set($config);

		$this->setData($data);
	}

/**
 * Config setter
 *
 * @param      $key
 * @param null $val
 * @return $this
 * @throws InvalidArgumentException
 */
	public function set($key, $val = null) {
		if (is_array($key)) {
			foreach ($key as $_k => $_v) {
				$this->set($_k, $_v);
			}
			return $this;
		} elseif (!is_string($key)) {
			throw new InvalidArgumentException('MediaUploader::set() - Given key is not a valid string');
		}

		$this->_config[$key] = $val;
		return $this;
	}

/**
 * Upload data setter
 *
 * @param $data
 * @return $this
 */
	public function setData($data) {
		$this->_data = $data;
		return $this;
	}

/**
 * Upload dir setter
 *
 * @param $path
 * @return $this
 * @throws UploadException
 */
	public function setUploadDir($path) {
		if (!is_dir($path) || !is_writeable($path)) {
			throw new UploadException(UPLOAD_ERR_CANT_WRITE);
		}

		return $this->set('uploadDir', $path);
	}

/**
 * Minimum upload file size in bytes
 *
 * @param int $size File size in bytes
 * @return $this
 */
	public function setMinFileSize($size) {
		return $this->set('minFileSize', (int)$size);
	}

/**
 * Maximum upload file size in bytes
 *
 * @param int $size File size in bytes
 * @return $this
 */
	public function setMaxFileSize($size) {
		return $this->set('maxFileSize', (int)$size);
	}

/**
 * Allowed upload file mime type(s)
 *
 * @param string|array $type
 * @return $this
 */
	public function setAllowedMimeType($type) {
		$this->set('allowedMimeTypes', $type);
	}

/**
 * Allowed upload file extension(s)
 *
 * @param string|array $ext
 * @return $this
 */
	public function setAllowedFileExtension($ext) {
		return $this->set('allowedFileExtensions', $ext);
	}

/**
 * Enable/Disable filename hashing
 *
 * @param $enable
 * @return $this
 */
	public function hashFilename($enable) {
		return $this->set('hashFilename', (bool)$enable);
	}

/**
 * Enable/Disable unique filename
 *
 * @param $enable
 * @return $this
 */
	public function uniqueFilename($enable) {
		return $this->set('uniqueFilename', (bool)$enable);
	}

/**
 * Get current configuration
 *
 * @return array
 */
	public function getConfig() {
		return $this->_config;
	}

/**
 * Perform upload
 */
	public function upload() {
		return $this->_upload($this->_data, $this->_config);
	}

/**
 * Upload Handler
 *
 * @param array $upload
 * @param array $config
 * @throws UploadException
 * @return array
 */
	protected function _upload($upload, $config) {
		// validate upload
		if (!$upload || !is_array($upload)) {
			throw new UploadException(UPLOAD_ERR_NO_FILE);
		}

		// check upload error
		if ($upload['error'] > 0) {
			throw new UploadException($upload['error']);
		}

		// check upload dir
		if (!is_dir($config['uploadDir']) || !is_writeable($config['uploadDir'])) {
			//$upload['error'] = UPLOAD_ERR_CANT_WRITE;
			debug('MediaUploader: Upload directory is not writeable (' . $config['uploadDir'] . ')');
			throw new UploadException(UPLOAD_ERR_CANT_WRITE);
		}

		// validate size limits and mime type
		if ($upload['size'] < $config['minFileSize']) {
			throw new UploadException(self::UPLOAD_ERR_MIN_FILE_SIZE);

		} elseif ($upload['size'] > $config['maxFileSize']) {
			throw new UploadException(self::UPLOAD_ERR_MAX_FILE_SIZE);

		} elseif (!MediaUtil::validateMimeType($upload['type'], $config['allowedMimeTypes'])) {
			throw new UploadException(self::UPLOAD_ERR_MIME_TYPE);
		}

		// split basename
		list($filename, $ext, $dotExt) = MediaUtil::splitBasename(trim($upload['name']));

		// validate extension
		if (!MediaUtil::validateFileExtension($ext, $config['allowedFileExtensions'])) {
			throw new UploadException(self::UPLOAD_ERR_FILE_EXT);
		}

		// filename
		$filename = Inflector::slug($filename, $config['filenameSlug']);

		// filename override
		if ($config['filename']) {
			$filename = basename($config['filename']);
		}

		// hash filename
		if ($config['hashFilename']) {
			$filename = sha1($filename);
		}

		// unique filename
		if ($config['uniqueFilename']) {
			$filename = uniqid($filename . $config['filenameSlug']);
		}

		$basename = $filename . $dotExt;
		$path = $config['uploadDir'];

		// build target file path
		$target = $path . $basename;
		if (file_exists($target) && $config['allowOverwrite'] == false) {
			$i = 0;
			$_filename = $filename;
			do {
				$filename = $_filename . '_' . ++$i;
				$basename = $filename . $dotExt;
				$target = $path . $basename;
			} while (file_exists($target) == true);
		}

		debug("Uploading file to " . $target);

		//move uploaded file to upload dir
		//TODO StorageEngine
		if (is_uploaded_file($upload['tmp_name'])) {
			if (!move_uploaded_file($upload['tmp_name'], $target)) {
				throw new UploadException(self::UPLOAD_ERR_STORE_UPLOAD);
			}

		} elseif (!copy($upload['tmp_name'], $target)) {
			throw new UploadException(self::UPLOAD_ERR_STORE_UPLOAD);
		}

		return array(
			'name' => $upload['name'], // file.txt
			'type' => $upload['type'], // text/plain
			'size' => $upload['size'], // 1234
			'path' => $target, // /path/to/uploaded/file
			'basename' => $basename, // file.txt
			'filename' => $filename, // file
			'ext' => $ext, // txt
			'dotExt' => $dotExt, // .txt
			'ts' => time(),
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
			UPLOAD_ERR_INI_SIZE => __("Maximum ini file size exceeded (%s)", ini_get('upload_max_filesize')),
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

		if (isset($errors[$errCode])) {
			return $errors[$errCode];
		}

		return __("Unknown upload error");
	}
}