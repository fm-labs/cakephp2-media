<?php
App::uses('ModelBehavior', 'Model');
App::uses('Cache','Cache');
App::uses('String', 'Utility');
App::uses('LibPhpThumb','Media.Lib');

defined('MEDIA_CACHE_DIR') or define('MEDIA_CACHE_DIR', CACHE . "media" . DS);
defined('MEDIA_THUMB_CACHE_DIR') or define('MEDIA_THUMB_CACHE_DIR', IMAGES . "cache" . DS);
defined('MEDIA_UPLOAD_TMP_DIR') or define('MEDIA_UPLOAD_TMP_DIR', TMP . "attachments" . DS);
defined('MEDIA_UPLOAD_DIR') or define('MEDIA_UPLOAD_DIR', WWW_ROOT . "uploads" . DS);

class AttachableBehavior extends ModelBehavior {

	const UPLOAD_ERR_MIN_FILE_SIZE = 100;
	const UPLOAD_ERR_MAX_FILE_SIZE = 101;
	const UPLOAD_ERR_MIME_TYPE = 102;
	const UPLOAD_ERR_FILE_EXT = 103;
	const UPLOAD_ERR_FILE_EXISTS = 104;
	const UPLOAD_ERR_STORE_TMP_UPLOAD = 105;
	const UPLOAD_ERR_STORE_UPLOAD = 106;
	const UPLOAD_ERR_CACHE_READ = 107;
	const UPLOAD_ERR_CACHE_WRITE = 108;

	const CACHE_CONFIG = 'media_upload';
	
	const CACHE_KEY_INSERTSTRING = '@:cacheKey@';
	
	const DEFAULT_PREVIEW_W = 125;
	const DEFAULT_PREVIEW_H = 125;
	const DEFAULT_PREVIEW_Q = 60;
	
	public $defaultConfig = array(
		'uploadField' => 'file_upload',
		'baseDir' => MEDIA_UPLOAD_DIR,
		'multiple' => false, // If FALSE, only one attachment will be stored. On edit file gets overwritten automatically
		'preserve' => true, // If 'multiple' is TRUE, 'preserve' (if TRUE) preserves files on edit, otherwise files get overwritten/deleted
		'minFileSize' => 0,
		'maxFileSize' => 2097152, //2MB
		'allowEmpty' => true, //Allow field to be empty
		'allowOverwrite' => false,
		'allowedMimeType' => '*', //"*" for all or array('image/*,text/plain). 
		'allowedFileExtension' => '*', //"*" for all or array('jpg','jpeg')
		'hashFilename' => false,
		'slug' => '_',
		'removeOnDelete' => true, //remove file if row gets deleted
		'preview' => false, //TRUE for standard preview, key/config pairs for custom sizes. Applies only to images. Requires phpThumb
		'thumbDir' => MEDIA_THUMB_CACHE_DIR
	);
	
	protected $_flaggedForRemoval = array();
	
	protected $_runtime = array();
	
	public function __construct() {
		parent::__construct();
		
		if (!Cache::config(self::CACHE_CONFIG)) {
			Cache::config(self::CACHE_CONFIG,array(
				'engine' => 'File',
				'duration' => 300,
				'prefix' => 'attachable_',
				'probability' => 100,
				'serialize' => true,
				'path' => MEDIA_CACHE_DIR,
			));
		}
	}
	
	/**
	 * @see ModelBehavior::setup()
	 * @param $settings
	 */
	public function setup(Model $model, $settings = array()) {
		
		if (!isset($this->settings[$model->alias])) {
			$attachments = (isset($model->attachments)) ? $model->attachments : array();
			$this->configureAttachment($model, $attachments);
		}
	}
	
	/**
	 * Setup Field
	 * 
	 * @param string $field
	 * @param mixed $config
	 * @param boolean $reset
	 */
	public function configureAttachment(Model $model, $field, $config = array(), $reset = false) {
		
		if (is_array($field)) {
			foreach($field as $_field => $_config) {
				$this->configureAttachment($model, $_field, $_config, $reset);
			}
			return; 
		}
		
		if ($reset || !$this->_getConfig($model, $field))
			$config = am($this->defaultConfig, array('uploadField'=>$field.'_upload'), $config);
		else
			$config = am($this->settings[$model->alias][$field], $config);
		
		//TODO check if directories exist and are writeable
		
		$this->settings[$model->alias][$field] = $config;
	}
	
	protected function _getConfig(Model $model, $field) {
		if (!isset($this->settings[$model->alias][$field]))
			return false;
		
		return $this->settings[$model->alias][$field];
	}
	
	protected function _getFields(Model $model) {
		return array_keys($this->settings[$model->alias]);
	}
	
	/**
	 * Configure the AttachableBehavior for the next find operation on the model
	 * 
	 * @param Model $model
	 * @param string $field		If FALSE, attachments won't be attached. Otherwise field name.
	 * @param array $config		Runtime config for given $field. If $field is set and $config is FALSE no attachment for given field will be attached
	 * @return Model
	 */
	public function attachment(Model $model, $field, $config = null) {
		if ($field === false) {
			$this->_runtime[$model->alias]['attachment'] = false;
			return $model;
		}
		
		$this->_runtime[$model->alias]['attachment'][$field] = $config;
		return $model;
	}
	
	public function beforeFind(Model $model, $query) {
		
		$attachRuntime = null;
		if (isset($this->_runtime[$model->alias]['attachment'])) {
			$attachRuntime = $this->_runtime[$model->alias]['attachment'];
		}
		
		$attachQuery = null;
		if (isset($query['attachment'])) {
			$attachQuery = $query['attachment'];
			unset($query['attachment']);
		}
		
		$runtime = array();
		foreach(array($attachRuntime, $attachQuery) as $attachment) {
			
			if ($attachment === null) 
				continue;
			
			if ($attachment === false || !is_array($attachment)) {
				$runtime = false;
				break;
			}
			
			foreach((array)$attachment as $field => $config) {
				$runtime[$field] = $config;
			}
		}
		
		$this->_runtime[$model->alias]['attachment'] = $runtime;
		
		return $query;
	}
	
	/**
	 * Append Attachment data to results
	 * 
	 * @see ModelBehavior::afterFind()
	 */
	public function afterFind(Model $model, $results, $primary) {
		
		$attachment = true;
		if (isset($this->_runtime[$model->alias]['attachment'])) {
			$attachment = $this->_runtime[$model->alias]['attachment'];
			unset($this->_runtime[$model->alias]);
		}

		if ($attachment === false)
			return $results;
		
		if ($primary) {
			
			foreach($results as &$result) {
				
				//check if any field is set
				foreach($this->_getFields($model) as $field) {
					
					//check runtime config
					if (is_array($attachment) && array_key_exists($field, $attachment)) {
						$runtime = $attachment[$field];
						if (!$runtime)
							continue;
					}
					
					if (!isset($result[$model->alias][$field]))
						continue;
					
					$config = $this->_getConfig($model, $field);
					
					//TODO read attachment cache for result
					
					//parse attachments
					$attachments = $this->_getAttachments($result[$model->alias][$field], $config);
					
					//TODO write attachment cache
					
					$data = $attachments;
					
					$result['Attachment'][$field] = $data;
				}
				
			}
		} else {
			//TODO afterFind non-primary results
		}
		
		return $results;
	}
	
	/**
	 * Check for HTML uploads and store them as temporary upload
	 * 
	 * @see ModelBehavior::beforeValidate()
	 */
	public function beforeValidate(Model $model, $options = array()) {
		
		if (isset($options['attachment'])) {
			$options = $options['attachment'];
			unset($options['attachment']);
		}
		
		$this->_uploadTemporary($model, $options);
		return true;
	}
	
	public function attachTemporary(Model $model, $data = null, $options = array()) {

		
		if ($data !== null) {
			$model->create();
			$model->set($data);
		}
		return $this->_uploadTemporary($model, $options);
	}
	
	/**
	 * Upload temporary
	 * 
	 * @param Model $model
	 * @param mixed $options Overrides default behavior settings
	 * @return boolean
	 */
	protected function _uploadTemporary(Model $model, $options = array()) {
		
		if (!$model->data)
			return false;
		
		//cacheKey
		$cacheKey = null;
		if (is_string($options))
			$cacheKey = $options;
		elseif(isset($options['cacheKey'])) {
			$cacheKey = $options['cacheKey'];
			unset($options['cacheKey']);
		}
		
		$uploadDetected = false;
		//check if any field is set
		foreach($this->_getFields($model) as $field) {
			$fieldConfig = $this->_getConfig($model, $field);
			$config = am($fieldConfig, $options);
			
			//detect upload
			if (isset($model->data[$model->alias][$config['uploadField']])) {
			
				$value = $model->data[$model->alias][$config['uploadField']];
				
				if (is_array($value)) {
					$formFiles = $value;
					if (!isset($formFiles[0])) {
						$formFiles = array($formFiles);
					}
					
					foreach($formFiles as $idx => &$upload) {
						try {
							//no upload
							if ($upload['error'] == UPLOAD_ERR_NO_FILE && $config['allowEmpty']) {
								unset($formFiles[$idx]);
								continue;
							}
							
							//check upload and upload temporary
							$this->_upload($upload, $config);
							
							//overwrite field. need to 
							if (isset($model->data[$model->alias][$field])) {
								if ($config['multiple'] && $config['preserve']) {
									//we need to preserve to 'old' attachments
									$upload['__preserve'] = $model->data[$model->alias][$field];
								} else {
									//overwrite. flag 'old' attachment for removal
									$this->_flagForRemoval($model, $model->id, $field, $model->data[$model->alias][$field]);
								}
							}
						} catch(Exception $e) {
							$model->invalidate($field, $upload['name'].': '.$e->getMessage());
							continue;
						}
					}
					
					//upload field was set, but no files submitted
					if (empty($formFiles)) {
						unset($model->data[$model->alias][$config['uploadField']]);
						continue;
					}
					
					$uploadDetected = true;
					
					$cacheKeyString = $this->_writeUploadCache($formFiles, $cacheKey);
					
					$model->data[$model->alias][$config['uploadField']] = $cacheKeyString;
					//TODO preserve field data on edit / delete files on overwrite
					$model->data[$model->alias][$field] = $cacheKeyString;
					$model->data['Attachment'][$field] = $formFiles;
				} else {
					//ignore non array values
					//$value = null;
					//throw new AttachableUploadException(UPLOAD_ERR_NO_FILE); 
				}
				
			}
			else {
				//upload field not set
				continue;
			}
			
		}
		
		return $uploadDetected;
	}
	
	/**
	 * 
	 * @param array $formFiles
	 * @throws Exception
	 * @return Ambigous <string, mixed, string>
	 */
	protected function _writeUploadCache($formFiles, $cacheKey = null) {
		
		if (!$cacheKey)
			$cacheKey = self::generateCacheKey();
		
		if (!Cache::write($cacheKey, $formFiles, self::CACHE_CONFIG))
			throw new AttachableUploadException(self::UPLOAD_ERR_CACHE_WRITE);

		return self::getCacheKeyString($cacheKey);
	}
	
	protected function _readUploadCache($cacheKey) {
		return Cache::read($cacheKey, self::CACHE_CONFIG);
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
		if ($upload['error'] > 0) {
			throw new AttachableUploadException($upload['error']);
		}
	
		//check upload dir
		if (!is_dir(MEDIA_UPLOAD_TMP_DIR) || !is_writeable(MEDIA_UPLOAD_TMP_DIR)) {
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
		list($filename,$ext, $dotExt) = $this->_splitBasename(trim($upload['name']));
	
		//validate extension
		if (!$this->_validateFileExtension($ext, $config['allowedFileExtension']))
			throw new AttachableUploadException(self::UPLOAD_ERR_FILE_EXT);
	
		if ($config['hashFilename'])
			$filename = sha1($filename);
		elseif($config['slug'])
			$filename = Inflector::slug($filename,$config['slug']);
	
		//TODO validate filename
	
		$upload['name'] = $filename.$dotExt;
	
		//safe temporary file path
		$i = 0;
		do {
			$tmpTarget = MEDIA_UPLOAD_TMP_DIR . uniqid($filename) . "-". $i++ . $dotExt;
		} while(file_exists($tmpTarget));
	
		//move uploaded file to tmp upload dir
		if (is_uploaded_file($upload['tmp_name'])) {
			if (!move_uploaded_file($upload['tmp_name'], $tmpTarget))
				throw new AttachableUploadException(self::UPLOAD_ERR_STORE_TMP_UPLOAD);
				
		} else {
			//TODO use a file engine here. Something like $this->_engine->storeTemporaryUpload($upload);
			if (!copy($upload['tmp_name'], $tmpTarget))
				throw new AttachableUploadException(self::UPLOAD_ERR_STORE_TMP_UPLOAD);
		}
	
		//update tmp name
		$upload['tmp_name'] = $tmpTarget;
	}
	
	public function onError($model, $error) {
		
		debug($error);
	}
	
	public function beforeSave(Model $model, $options = array()) {
		return true;
	}
	
	/**
	 * Check for uploaded files and store them
	 * 
	 * @see ModelBehavior::afterSave()
	 */
	public function afterSave(Model $model, $created) {

		if (!$model->data)
			return true;		
		
		//check if any field is set
		foreach($this->_getFields($model) as $field) {
			$config = $this->_getConfig($model, $field);
			
			if (isset($model->data[$model->alias][$field])) {
			
				$upload = $model->data[$model->alias][$field];
					
				//START tmp upload
				if (($cacheKey = self::getCacheKey($upload)) != false) {
					
					$tmpUploads = $this->_readUploadCache($cacheKey);
					//TODO what if cacheKey has expired
					
					$attachments = array();
					foreach((array)$tmpUploads as $tmpUpload) {
						
						try {
							
							//check for preserved attachments
							if (isset($tmpUpload['__preserve'])) {
								$preserve = $tmpUpload['__preserve'];
								unset($tmpUpload['__preserve']);
								
								$_attachments = $this->_getAttachments($preserve, $config);
								$attachments += $_attachments;
							}
							
							$attachment = $this->_store($tmpUpload, $config);
							array_push($attachments, $attachment);
						} catch(AttachableUploadException $e) {
							debug($e->getMessage());
							$model->invalidate($config['uploadField'], $tmpUpload['name'].': '. $e->getMessage());
						} catch(Exception $e) {
							debug($e->getMessage());
							$model->invalidate($config['uploadField'], $tmpUpload['name'].': '.__('An internal error occured'));
							$this->log($e->getMessage(), 'error');
						}
						
					}
					
					$basenames = Hash::extract($attachments, '{n}.basename');
					
					//save basenames in $field
					$fileStr = join(',',$basenames);
					
					//assign data
					$model->data['Attachment'][$field] = $attachments;
					$model->data[$model->alias][$field] = $fileStr;
					unset($model->data[$model->alias][$config['uploadField']]);
					
					//need to clone model before saveField()
					//otherwise the result of the parent save() action would be boolean
					$modelClone = clone $model;
					$modelClone->id = $model->id;
					if (!$modelClone->saveField($field, $fileStr))
						return false;
					
					unset($modelClone);
					
					//clear cache
					Cache::delete($cacheKey, self::CACHE_CONFIG);
					
				} // END tmp upload
			}
		}		
		
		$this->_removeFiles($model);
		
		return true;
	}
	

	/**
	 * Move temporary upload to destination
	 *
	 * @param array $tmpUpload
	 * @param string $dest Filepath to destination
	 * @throws AttachableUploadException
	 */
	protected function _store($tmpUpload, $config) {
	
		$basename = $tmpUpload['name'];
		list($filename, $ext, $dotExt) = $this->_splitBasename($basename);
	
		//safe target
		$path = $config['baseDir'] . $basename;
		if (file_exists($path) && !$config['allowOverwrite']) {
			$_filename = $filename;
			$i = 0;
			do {
				$filename = $_filename.$config['slug'].++$i;
				$basename = $filename.$dotExt;
				$path = $config['baseDir'].$basename;
			} while(file_exists($path) == true);
		}
	
		//move temporary file
		$tmpPath = $tmpUpload['tmp_name'];
		if (!copy($tmpUpload['tmp_name'], $path))
			throw new AttachableUploadException(self::UPLOAD_ERR_STORE_UPLOAD);
		
		unlink($tmpPath);
	
		//attachment data
		$attachment = array(
				'path' => $path,
				'basename' => $basename,
				'filename' => $filename,
				'ext' => $ext,
				//'dotExt' => $dotExt,
				//'size' => $tmpUpload['size'],
				//'type' => $tmpUpload['type'],
				//'error' => $tmpUpload['error'],
		);
		
		//TODO trigger event 'afterStore'. Use for creating preview
		$attachment = $this->_createPreview($attachment, $config);
		
		return $attachment;
	}
	
	protected function _createPreview($attachment, $config) {
		
		//check if enabled in config
		if ($config['preview'] === false)
			return $attachment;
		
		//check if preview can be created for this file extension
		if (!$this->_validateFileExtension($attachment['ext'], array('jpg','jpeg','png','gif'))) {
			return $attachment;
		}

		//TODO validate mime type
		//if (!$this->_validateMimeType($attachment['type'], 'image/*'))
		//	return $attachment;
		
		//default preview params
		if ($config['preview'] === true) {
			$config['preview'] = array('default' => array(
				'width'=>self::DEFAULT_PREVIEW_W,
				'height'=>self::DEFAULT_PREVIEW_H,
				'quality'=>self::DEFAULT_PREVIEW_Q,
			));
		}
		
		//create thumbs
		foreach($config['preview'] as $size => $params) {
			$thumbSource = $attachment['path'];
			$thumbTarget = $config['thumbDir'] . $this->_getPreviewName($attachment, $size, $config);
			
			try {
				$thumbPath = LibPhpThumb::createThumbnail($thumbSource, $thumbTarget, $params);
			} catch(Exception $e) {
				$thumbPath = false;
				$this->log('AttachableBehavior::_createPreview(): '.$e->getMessage(), 'error');
			}
			$attachment['preview'][$size] = $thumbPath;
		}
		return $attachment;
	}
	
	protected function _getPreviewName($attachment, $size, $config) {
		
		list ($filename, $ext, $dotExt) = $this->_splitBasename($attachment['basename']);
		
		return $filename . $config['slug'] . $size .$config['slug'] . md5($attachment['path']) . $dotExt;
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
		
		foreach($fields as $field) {
			$config = $settings[$field];
			
			//do not remove files
			if (!$config['removeOnDelete'])
				continue;
			
			if (!isset($data['Attachment']))
				//throw new Exception("Attachment data missing");
				continue;
			
			//flag attachments for removal
			if (isset($data['Attachment'][$field])) 
			{
				
				foreach($data['Attachment'][$field] as $idx => $attachment) {
					$this->_flagForRemoval($model, $model->id, $field, $attachment['basename']);
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
		
		return $config['baseDir'] . $filename;
	}
	
	/**
	 * Flag $filepath to be removed afterDelete
	 * 
	 * @param Model $model
	 * @param string $filepath
	 * @return void
	 */
	protected function _flagForRemoval(Model $model, $id, $field, $fileStr = null) {
		$config = $this->settings[$model->alias][$field];
		
		foreach($this->_getAttachments($fileStr, $config) as $_attachment) {
			$this->_flaggedForRemoval[$model->alias][$id][] = $_attachment['path'];
		}
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
	
	/**
	 * @see AttachableBehavior::splitBasename()
	 * @param string $basename
	 * @deprecated Use the static function instead
	 */
	protected function _splitBasename($basename) {
		return self::splitBasename($basename);
	}
	
	static public function splitBasename($basename) {
		
		if (strrpos($basename,'.') !== false) {
			$parts = explode('.', $basename);
			$ext = array_pop($parts);
			$dotExt = '.'.$ext;
			$filename = join('.',$parts);
		} else {
			$ext = $dotExt = null;
			$filename = $basename;
		}
		
		return array($filename, $ext, $dotExt);
		
	}
	
	static public function generateCacheKey() {
		return String::uuid();	
	}
	
	static public function getCacheKeyStringPattern() {
		return '/^'.self::getCacheKeyString('(.*)').'$/';
	}

	static public function getCacheKeyString($cacheKey) {
		return String::insert(self::CACHE_KEY_INSERTSTRING, array('cacheKey'=>$cacheKey));
	}
	
	static public function getCacheKey($cacheKeyString) {
		
		if (preg_match(self::getCacheKeyStringPattern(), $cacheKeyString, $matches)) {
			return $matches[1];
		}
		
		return false;
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
			AttachableBehavior::UPLOAD_ERR_CACHE_READ => __("Failed reading tmp upload from cache"),
			AttachableBehavior::UPLOAD_ERR_CACHE_WRITE => __("Failed writting tmp upload to cache")
		);
	
		if (isset($errors[$errCode]))
			return $errors[$errCode];
	
		return __("Unknown upload error");
	}	
}