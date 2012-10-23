<?php
App::uses('ModelBehavior', 'Model');

defined('MEDIA_TMP_UPLOAD_DIR') or define('MEDIA_TMP_UPLOAD_DIR', TMP . "attachments" . DS);
defined('MEDIA_DEFAULT_UPLOAD_DIR') or define('MEDIA_DEFAULT_UPLOAD_DIR', WWW_ROOT . "uploads" . DS);

class AttachableBehavior extends ModelBehavior {

	const UPLOAD_ERR_MIN_FILE_SIZE = 100;
	const UPLOAD_ERR_MAX_FILE_SIZE = 101;
	const UPLOAD_ERR_MIME_TYPE = 102;
	const UPLOAD_ERR_FILE_EXT = 103;
	const UPLOAD_ERR_FILE_EXISTS = 104;
	const UPLOAD_ERR_STORE_TMP_UPLOAD = 105;
	const UPLOAD_ERR_STORE_UPLOAD = 106;
	
	public $defaultSettings = array(
		'dir' => MEDIA_DEFAULT_UPLOAD_DIR,
		'multiple' => false,
		'minFileSize' => 0,
		'maxFileSize' => 2097152, //2MB
		'allowedMimeType' => '*', //"*" for all or array('image/*,text/plain). 
		'allowedFileExtension' => '*', //"*" for all or array('jpg','jpeg')
		'hashFilename' => false,
		'slug' => '_',
		'removeOnDelete' => true, //remove file if row gets deleted
	);
	
	protected $_flaggedForRemoval = array();
	
	/**
	 * @see ModelBehavior::setup()
	 * @param $settings
	 */
	public function setup(Model $model, $settings = array()) {
		
		if (!isset($this->settings[$model->alias])) {
			
			if (isset($model->attachments)) {
				$attachments = $model->attachments;
			} else {
				$attachments = array();
			}
			
			//TODO apply settings to each attachment
			foreach($attachments as $field => &$config) {
				$config = am($this->defaultSettings, $config);
			}
			
			$this->settings[$model->alias] = $attachments;
		}
	}
	
	public function beforeFind(Model $model, $query) {
		
		return $query;
	}
	
	public function afterFind(Model $model, $results, $primary) {
		
		$settings = $this->settings[$model->alias];
		
		if ($primary) {
			
			foreach($results as &$result) {
				
				//check if any field is set
				$fields = array_keys($settings);
				foreach($fields as $field) {
					if (!isset($result[$model->alias][$field]))
						continue;
					
					$config = $settings[$field];
					
					//TODO read cache for result
					
					//parse attachments
					$attachments = $this->_getAttachments($result[$model->alias][$field], $config);
					
					//TODO write cache
					
					$data = array();
					if ($config['multiple'] === true) {
						$data = $attachments;
					} elseif (isset($attachments[0])) {
						$data = $attachments[0];
					}
					
					$result['Attachment'][$field] = $data;
				}
				
			}
		}
		
		return $results;
	}
	
	/**
	 * Check for HTML uploads and store them as temporary upload
	 * 
	 * @see ModelBehavior::beforeValidate()
	 */
	public function beforeValidate(Model $model) {
		
		$settings = $this->settings[$model->alias];
		
		if (!$model->data)
			return true;
		
		//check if any field is set
		$fields = array_keys($settings);
		foreach($fields as $field) {
			if (!isset($model->data[$model->alias][$field]))
				continue;
			
			$value = $model->data[$model->alias][$field];
			$config = $settings[$field];
			
			//detect upload
			if (is_array($value)) {
				$uploadedFiles = $value;
				if (!isset($uploadedFiles[0])) {
					$uploadedFiles = array($uploadedFiles);
				}
				
				foreach($uploadedFiles as &$upload) {
					try {
						$this->_upload($upload, $config);
					} catch(Exception $e) {
						$model->invalidate($field, $e->getMessage());
						return false;
					}
				}
				
				$value = '@'.serialize($uploadedFiles).'@';
				
			} else {
				//no upload
			}
			
			$model->data[$model->alias][$field] = $value;
		}
		
		return true;
	}
	
	/**
	 * Check for uploaded files and store them
	 * 
	 * @see ModelBehavior::beforeSave()
	 */
	public function beforeSave(Model $model) {

		$settings = $this->settings[$model->alias];
		
		if (!$model->data)
			return true;		
		
		//check if any field is set
		$fields = array_keys($settings);
		foreach($fields as $field) {
			if (!isset($model->data[$model->alias][$field]))
				continue;
			
			$value = $model->data[$model->alias][$field];
			$config = $settings[$field];
				
			//detect temp upload
			if (preg_match('/^@(.*)@$/', $value, $matches)) {
				
				$tmpUploads = unserialize($matches[1]);

				$attachments = array();
				foreach($tmpUploads as &$tmpUpload) {
					
					$basename = $tmpUpload['basename'];
					list($filename, $ext) = $this->_splitBasename($basename);
					$path = $config['dir'] . $basename;
					
					try {
						$this->_store($tmpUpload, $path);
					} catch(Exception $e) {
						$model->invalidate($field, $e->getMessage());
						return false;
					}
					
					$attachment = compact('path','basename','filename','ext');
					array_push($attachments, $attachment);
					
				}
					
				$basenames = array();
				if ($config['multiple'] === true) {
					$basenames = Hash::extract($attachments, '{n}.basename');
				} elseif (isset($attachments[0])) {
					$attachments = $attachments[0];
					$basenames = array($attachments['basename']);
				}
				
				//save basenames in $field
				$value = join(',',$basenames);
					
				//assign Attachment(s)
				$model->data['Attachment'][$field] = $attachments;
				
				//TODO write cache
			}
			
			$model->data[$model->alias][$field] = $value;
			
		}		
		
		return true;
	}
	
	/**
	 * Check attached files should be removed and flag them. 
	 * After table row deletion was successful, the attached files will be removed.
	 * 
	 * @see ModelBehavior::beforeDelete()
	 */
	public function beforeDelete($model) {
		
		$settings = $this->settings[$model->alias];
		$fields = array_keys($settings);
		
		$data = $model->read($fields,$model->id);
		debug($data);
		
		foreach($fields as $field) {
			$config = $settings[$field];
			
			//do not remove files
			if (!$config['removeOnDelete'])
				continue;
			
			if (!isset($data['Attachment']))
				throw new Exception("Attachment data missing");
			
			//flag attachments for removal
			if (isset($data['Attachment'][$field])) 
			{
				$attachments = (isset($data['Attachment'][$field][0])) 
					? $data['Attachment'][$field] : array($data['Attachment'][$field]);
				
				foreach($attachments as $attachment) {
					$this->_flagForRemoval($model, $attachment['path']);
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Remove flagged files after delete
	 * 
	 * @see ModelBehavior::afterDelete()
	 */
	public function afterDelete($model) {
		
		$this->_removeFiles($model);
		
		return true;
	}
	
	/**
	 * Convert field values to Attachment data set
	 * 
	 * @param string|array $value Array or comma-separated string 
	 * @param array $config Field config
	 * @return array
	 */
	protected function _getAttachments($valueList = '', $config) {
		
		$attachments = array();
		$path = $filename = $basename = $ext = $url = null;
		if (is_string($valueList))
			$files = explode(',',$valueList);
		elseif(is_array($valueList))
			$files = $valueList;
		else
			throw new InvalidArgumentException("Invalid value list of type ".gettype($valueList));
		
		foreach($files as $file) {
			$file = trim($file);
			if (!$file)
				continue;
		
			$basename = $file;
			$path = $this->_getFilePath($file, $config);
		
			list($filename, $ext) = $this->_splitBasename($basename);
		
			$attachment = compact('path','basename','filename','ext');
			array_push($attachments, $attachment);
		}
		
		return $attachments;
	}
	
	/**
	 * Get full file path from filename and field config
	 * 
	 * @param string $filename
	 * @param array $config
	 * @return boolean|string
	 */
	protected function _getFilePath($filename = null, $config) {
		if (!$filename)
			return false;
		
		return $config['dir'] . $filename;
	}
	
	/**
	 * Flag $filepath to be removed afterDelete
	 * 
	 * @param Model $model
	 * @param string $filepath
	 * @return void
	 */
	protected function _flagForRemoval(Model $model, $filepath) {
		$this->_flaggedForRemoval[$model->alias][$model->id][] = $filepath;
	}
	
	/**
	 * Remove flagged files for given model
	 * 
	 * @param Model $model
	 * @return void
	 */
	protected function _removeFiles(Model $model) {
		
		if (!isset($this->_flaggedForRemoval[$model->alias]) 
			|| !isset($this->_flaggedForRemoval[$model->alias][$model->id])
			|| empty($this->_flaggedForRemoval[$model->alias][$model->id]))
		{
			return; 
		}
		
		foreach($this->_flaggedForRemoval[$model->alias][$model->id] as $idx => $filepath) {
			if (!file_exists($filepath) || !is_file($filepath)) {
				$this->log(__("Skip Delete for attachment for Model %s [%s] (File not found): %s", $model->alias, $model->id, $filepath),'debug');
				unset($this->_flaggedForRemoval[$model->alias][$model->id][$idx]);
			}
			elseif (@unlink($filepath)) {
				$this->log(__("Deleted attachment for Model %s [%s]: %s", $model->alias, $model->id, $filepath),'debug');
				unset($this->_flaggedForRemoval[$model->alias][$model->id][$idx]);
			} 
			else {
				$this->log(__("Failed to delete attachment for Model %s [%s]: %s", $model->alias, $model->id, $filepath),'error');
			}
		}
	}
	
	/**
	 * Temporary Upload
	 * 
	 * @param array $upload Mulitpart form upload data
	 * @param array $config Field config
	 * @throws AttachableUploadException
	 */
	protected function _upload(&$upload, $config) {
		
		//validate upload error
		if ($upload['error'] > 0)
			throw new AttachableUploadException($upload['error']);
		
		//check upload dir
		if (!is_dir(MEDIA_TMP_UPLOAD_DIR) || !is_writeable(MEDIA_TMP_UPLOAD_DIR)) {
			$upload['error'] = UPLOAD_ERR_CANT_WRITE;
		}
		
		//validate size
		elseif ($upload['size'] < $config['minFileSize'])
			throw new AttachableUploadException(self::UPLOAD_ERR_MIN_FILE_SIZE);
		elseif ($upload['size'] > $config['maxFileSize'])
			throw new AttachableUploadException(self::UPLOAD_ERR_MAX_FILE_SIZE);
		
		//validate mime
		elseif (!$this->_validateMimeType($upload['type'], $config['allowedMimeType']))
			throw new AttachableUploadException(self::UPLOAD_ERR_MIME_TYPE);

		//split basename
		list($filename,$ext) = $this->_splitBasename(trim($upload['name']));
		
		//validate extension
		if (!$this->_validateFileExtension($ext, $config['allowedFileExtension']))
			throw new AttachableUploadException(self::UPLOAD_ERR_FILE_EXT);
		
		
		if ($config['hashFilename'])
			$filename = sha1($filename);
		elseif($config['slug'])
			$filename = Inflector::slug($filename,$config['slug']);
		
		//TODO validate filename
		
		$_ext = ($ext) ? '.'.$ext : '';
		$upload['basename'] = $filename.$_ext;
		
		//safe temporary file path
		$i = 0;
		do {
			$tmpTarget = MEDIA_TMP_UPLOAD_DIR . uniqid($filename) . "-". $i++ . $_ext;
		} while(file_exists($tmpTarget));
		
		//move uploaded file to tmp upload dir
		if (is_uploaded_file($upload['tmp_name'])) {
			if (!move_uploaded_file($upload['tmp_name'], $tmpTarget))				
				throw new AttachableUploadException(self::UPLOAD_ERR_STORE_TMP_UPLOAD);
			
		} else {
			if (!copy($upload['tmp_name'], $tmpTarget))
				throw new AttachableUploadException(self::UPLOAD_ERR_STORE_TMP_UPLOAD);
		}
		
		//store tmp path
		$upload['path'] = $tmpTarget;
	}
	
	/**
	 * Move temporary upload to destination
	 * 
	 * @param array $tmpUpload
	 * @param string $dest Filepath to destination
	 * @throws AttachableUploadException
	 */
	protected function _store($tmpUpload, $dest) {
		
		if (!copy($tmpUpload['path'], $dest))
			throw new AttachableUploadException(self::UPLOAD_ERR_STORE_UPLOAD);
		
		unlink($tmpUpload['path']);
	}
	
	protected function _validateMimeType($mime, $allowed = array()) {
		
		if (is_string($allowed)) {
			if($allowed == "*")
				return true;
			else
				$allowed = array($allowed);
		}
		
		$mime = explode('/',$mime);
		
		foreach($allowed as $type) {
			$type = explode('/', $type);
			if ($mime[0] != $type[0])
				continue;
			
			if ($type[1] == "*" || $mime[1] == $type[1])
				return true;
		}
		
		return false;
	}
	
	protected function _validateFileExtension($ext, $allowed = array()) {
	
		if (is_string($allowed)) {
			if($allowed == "*")
				return true;
			else
				$allowed = array($allowed);
		}
		
		return in_array($ext, $allowed);
	}
	
	protected function _validateFileName($filename, $pattern = null) {
		return true;
	}
	
	protected function _splitBasename($basename) {
		
		if (strrpos($basename,'.') !== false) {
			$parts = explode('.', $basename);
			$ext = array_pop($parts);
			$filename = join('.',$parts);
		} else {
			$ext = null;
			$filename = $basename;
		}
		
		return array($filename, $ext);
	}
	
	
}

class AttachableUploadException extends CakeException {
	
	public function __construct($errCode, $upload = null, $code = 500) {
		$message = $this->_mapError($errCode, $upload);
		parent::__construct($message, $code);
	}

	protected function _mapError($errCode, $upload) {
	
		$errors = array(
			UPLOAD_ERR_OK => __("Upload successful"),
			UPLOAD_ERR_INI_SIZE => __("Maximum file size exceeded"),
			UPLOAD_ERR_FORM_SIZE => __("Maximum form file size exceeded"),
			UPLOAD_ERR_PARTIAL => __("File only partially uploaded"),
			UPLOAD_ERR_NO_FILE => __("No file uploaded"),
			UPLOAD_ERR_NO_TMP_DIR => __("Upload directory missing"), //PHP 5.0.3+
			UPLOAD_ERR_CANT_WRITE => __("Cant write to upload directory"), //PHP 5.1.0+
			UPLOAD_ERR_EXTENSION => __("Upload extension error"), //PHP 5.2.0+
			AttachableBehavior::UPLOAD_ERR_FILE_EXISTS => __('File already exists'),
			AttachableBehavior::UPLOAD_ERR_FILE_EXT => __('Invalid file extension'),
			AttachableBehavior::UPLOAD_ERR_MIME_TYPE => __('Invalid mime type'),
			AttachableBehavior::UPLOAD_ERR_MIN_FILE_SIZE => __('Minimum file size error'),
			AttachableBehavior::UPLOAD_ERR_MAX_FILE_SIZE => __("Maximum file size exceeded"),
			AttachableBehavior::UPLOAD_ERR_STORE_TMP_UPLOAD => __("Failed to store uploaded file temporary"),
			AttachableBehavior::UPLOAD_ERR_STORE_UPLOAD => __("Failed to store uploaded file"),
		);
	
		if (isset($errors[$errCode]))
			return $errors[$errCode];
	
		return __("Unknown upload error");
	}	
}