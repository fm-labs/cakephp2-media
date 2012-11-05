<?php
App::uses('ModelBehavior', 'Model');
App::uses('Cache','Cache');
App::uses('String', 'Utility');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
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
	
	const DEFAULT_PREVIEW_W = 60;
	const DEFAULT_PREVIEW_H = 60;
	const DEFAULT_PREVIEW_Q = 100;
	
	public $defaultConfig = array(
		'enabled' => true, // If TRUE attachments get auto attached afterFind
		'uploadField' => null, // fieldname which holds file upload. Defaults to FIELDNAME_upload.
		'uploadNameField' => null, // fieldname which holds target file name. Defaults to FIELDNAME_name.
		'baseDir' => MEDIA_UPLOAD_DIR,
		'subFolder' => '',
		'multiple' => false, // If FALSE, only one attachment will be stored. On edit file gets overwritten automatically
		'append' => true, // If 'multiple' is TRUE, 'append' (if TRUE) appends files on edit, otherwise files get overwritten/deleted
		'minFileSize' => 0,
		'maxFileSize' => 2097152, //2MB
		'allowEmpty' => true, //Allow field to be empty
		'allowOverwrite' => false,
		'allowedMimeType' => '*', //"*" for all or array('image/*,text/plain). 
		'allowedFileExtension' => '*', //"*" for all or array('jpg','jpeg')
		'hashFilename' => false,
		'slug' => '_',
		'removeOnDelete' => true, //remove file if row gets deleted
		'removeOnOverwrite' => true, //remove file if file has been replaced
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
		if (!isset($this->_runtime[$model->alias])) {
			$this->_runtime[$model->alias] = array();
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
			$config = am($this->defaultConfig, array(
				'uploadField'=>$field.'_upload',
				'uploadNameField'=>$field.'_name'), $config);
		else
			$config = am($this->settings[$model->alias][$field], $config);
		
		//TODO check if directories exist and are writeable
		
		$this->settings[$model->alias][$field] = $config;
	}
	
	/**
	 * @param unknown_type $model
	 * @return multitype:
	 * 
	 * @deprecated
	 */
	public function getConfig($model) {
		return $this->settings[$model->alias];
	}
	
	protected function _setRuntimeConfig(Model &$model, $field, $config = array()) {

		if (is_array($field)) {
			foreach($field as $_field => $_config) {
				$this->_setRuntimeConfig($model, $_field, $_config);
			}
			return;
		}
		elseif (is_bool($field)) {
			foreach($this->_getFields($model) as $_field) {
				$this->_setRuntimeConfig($model, $_field, array('enabled'=>$field));
			}
			return;
		} 
		elseif ($config === false) {
			$config =  array('enabled'=>false);
		}		
		
		$this->_runtime[$model->alias][$field] = $config;		
		
	}

	protected function _getRuntimeConfig(Model &$model, $field) {
		if (!isset($this->_runtime[$model->alias][$field]))
			return array();
		
		$config = $this->_runtime[$model->alias][$field];
		unset($this->_runtime[$model->alias][$field]);
		return $config;
	}
	
	protected function _getConfig(Model &$model, $field, $includeRuntimeConfig = true) {
		if (!isset($this->settings[$model->alias][$field]))
			return false;
		
		$config = $this->settings[$model->alias][$field];
		
		if ($includeRuntimeConfig)
			$config = am($config, $this->_getRuntimeConfig($model, $field));
		
		return $config;
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
	public function attachment(Model $model, $field, $config = array()) {
			
		$this->_setRuntimeConfig($model, $field, $config);
		
		return $model;
	}
	
	public function beforeFind(Model $model, $query) {
		
		if (isset($query['attachment'])) {
			$this->_setRuntimeConfig($model, $query['attachment']);
			unset($query['attachment']);
		}
		
		return $query;
	}
	
	/**
	 * Append Attachment data to results
	 * 
	 * @see ModelBehavior::afterFind()
	 */
	public function afterFind(Model $model, $results, $primary) {
		
		if ($primary) {
			
			foreach($results as &$result) {
				
				//check if any field is set
				foreach($this->_getFields($model) as $field) {
					
					if (!isset($result[$model->alias][$field]))
						continue;
					
					$config = $this->_getConfig($model, $field, true);
					
					if (!$config['enabled'])
						continue;
					
					//TODO read attachment cache for result
					
					//parse attachments
					$attachments = $this->_getAttachments($model, $result[$model->alias][$field], $config);
					
					//attach preview
					foreach($attachments as &$attachment)
						$attachment = $this->_attachPreview($attachment, $config);
					
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
		
		$options = (isset($options['attachment'])) ? $options['attachment'] : array();
		
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
		if (is_string($options)) {
			$cacheKey = $options;
			$options = array();
		} elseif(isset($options['cacheKey'])) {
			$cacheKey = $options['cacheKey'];
			unset($options['cacheKey']);
		}
		
		
		if ($options) {
			$this->_setRuntimeConfig($model, $options);
		}
		
		$uploadDetected = false;
		//check if any field is set
		foreach($this->_getFields($model) as $field) {
			$config = $this->_getConfig($model, $field, true);
			
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
							$this->_upload($model, $upload, $config);
							
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
					
					//transport current field value in upload as temp var
					$formFiles['__current__'] = (isset($model->data[$model->alias][$field])) ? trim($model->data[$model->alias][$field]) : '';
					$formFiles['__append__'] = ($config['multiple'] && $config['append']) ? true : false; 
					
					$uploadDetected = true;
					
					$cacheKeyString = $this->_writeUploadCache($formFiles, $cacheKey);
					
					$model->data[$model->alias][$config['uploadField']] = $cacheKeyString;
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
	protected function _upload(Model &$model, &$upload, $config) {
	
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
		list($filename,$ext, $dotExt) = self::splitBasename(trim($upload['name']));
	
		//validate extension
		if (!$this->_validateFileExtension($ext, $config['allowedFileExtension']))
			throw new AttachableUploadException(self::UPLOAD_ERR_FILE_EXT);
	
		//safe temporary file path
		$i = 0;
		do {
			//TODO use an object property instead of constant here?! 
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
	
	/**
	 * (non-PHPdoc)
	 * @see ModelBehavior::onError()
	 * @todo Implement onError() method
	 */
	public function onError($model, $error) {
		$this->log($error, LOG_ERR);
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
					if (!$tmpUploads)
						continue;
					
					$__current = $tmpUploads['__current__'];
					$__append = $tmpUploads['__append__'];
					unset($tmpUploads['__current__'],$tmpUploads['__append__']);
					
					$attachments = array();
					foreach((array)$tmpUploads as $tmpUpload) {
						
						try {
							
							$attachment = $this->_store($model, $tmpUpload, $config);
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
					
					
					//preserve or overwrite
					if ($config['multiple'] && $__append) {
						$attachments = am($this->_getAttachments($model, $__current, $config), $attachments);
					} 
					
					//extract basenames
					$basenames = Hash::extract($attachments, '{n}.basename');
					
					//check for changes files
					$fileForRemoval = $this->__getFilesForRemoval($__current, $basenames);
					foreach($fileForRemoval as $_fileName) {
						$this->_flagForRemoval($model, $model->id, $field, $_fileName);
					}
					
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
	
	private function __getFilesForRemoval($current = array(), $new = array()) {
		
		if (is_string($current))
			$current = explode(',',$current);
		
		if (is_string($new))
			$current = explode(',',$new);
		
		$diff = array_diff($current, $new);
		
		for($i=0;$i<count($diff);$i++) {
			if (!$diff[$i])
				unset($diff[$i]);
		}
		
		return $diff;
	}
	
	/**
	 * Move temporary upload to destination
	 *
	 * @param array $tmpUpload
	 * @param string $dest Filepath to destination
	 * @throws AttachableUploadException
	 */
	protected function _store(Model &$model, $tmpUpload, $config) {
	
		//create subfolders if needed
		$path = $this->_getBasePath($model, $config);
		$Folder = new Folder($path, true);
		if (!$Folder->cd($path))
			throw new AttachableUploadException(self::UPLOAD_ERR_STORE_UPLOAD);
		unset($Folder);
		
		 //build filename
		list($filename, $ext, $dotExt) = self::splitBasename($tmpUpload['name']);
		
		if (isset($model->data[$model->alias][$config['uploadNameField']]) 
			&& !empty($model->data[$model->alias][$config['uploadNameField']])) {
			
			//TODO validate filename
			$filename = trim($model->data[$model->alias][$config['uploadNameField']]);
		}
		
		if ($config['hashFilename'])
			$filename = sha1($filename);
		elseif($config['slug'])
			$filename = Inflector::slug($filename,$config['slug']);
		
		$basename = $filename.$dotExt;
		
		//build targetname
		$targetPath = $path . $basename;
		if (file_exists($targetPath) && $config['allowOverwrite'] == false) {
			$i = 0;
			$_filename = $filename;
			do {
				$filename = $_filename.'_'.++$i;
				$basename = $filename.$dotExt;
				$targetPath = $path.$basename;
			} while(file_exists($targetPath) == true);
		}
	
		//move temporary file
		$TmpFile = new File($tmpUpload['tmp_name'],false);
		$File = new File($targetPath, true);
		
		if (!$File->write($TmpFile->read()))
			throw new AttachableUploadException(self::UPLOAD_ERR_STORE_UPLOAD);
		$File->close();
		
		if (!$TmpFile->delete())
			$this->log(__('Failed to delete temporary upload file %s', basename($tmpUpload['tmp_name'])));
		
		//attachment data
		$attachment = array(
				'path' => $targetPath,
				'basename' => $basename,
				'filename' => $filename,
				'ext' => $ext,
				//'dotExt' => $dotExt,
				//'size' => $tmpUpload['size'],
				//'type' => $tmpUpload['type'],
				//'error' => $tmpUpload['error'],
		);
		
		//TODO trigger event 'afterStore'. Use for creating preview
		$attachment = $this->_attachPreview($attachment, $config);
		
		return $attachment;
	}
	
	protected function _attachPreview($attachment, $config) {
		
		$preview = $config['preview'];
		
		//check if enabled in config
		if ($preview === false)
			return $attachment;
		
		//check if preview can be created for this file extension
		if (!$this->_validateFileExtension($attachment['ext'], array('jpg','jpeg','png','gif'))) {
			return $attachment;
		}

		//TODO validate mime type
		//if (!$this->_validateMimeType($attachment['type'], 'image/*'))
		//	return $attachment;
		
		//default preview params
		if ($preview === true) {
			$preview = array();
		}
		
		if (!isset($preview['default']))
			$preview['default'] = array(
				'width'=>self::DEFAULT_PREVIEW_W,
				'height'=>self::DEFAULT_PREVIEW_H,
				'quality'=>self::DEFAULT_PREVIEW_Q,
			);
		
		$paramsMap = array(
			'width' => 'w',
			'height' => 'h',
			'quality' => 'q',	
		);
		
		//create thumbs
		foreach($preview as $size => $params) {
			
			//map preview params
			$_params = array();
			foreach($params as $k => $v) {
				if (array_key_exists($k, $paramsMap))
					$_params[$paramsMap[$k]] = $v;
				else
					$_params[$k] = $v;
			}
			
			$path = $url = false;
			
			try {
				list($path, $url) = LibPhpThumb::getThumbnail($attachment['path'], null, $_params);
			} catch(Exception $e) {
				debug($e->getMessage());
				$this->log('AttachableBehavior::_createPreview(): '.$e->getMessage(), 'error');
			}
			
			$attachment['preview'][$size] = compact('path', 'url');
		}
		return $attachment;
	}
	
	/**
	 * Check attached files should be removed and flag them. 
	 * After table row deletion was successful, the attached files will be removed.
	 * 
	 * @see ModelBehavior::beforeDelete()
	 */
	public function beforeDelete($model) {
		
		$fields = array_keys($this->settings[$model->alias]);
		$model->data = $model->read($fields,$model->id);
		
		foreach($fields as $field) {
			$config = $this->settings[$model->alias][$field];
			
			//do not remove files
			if (!$config['removeOnDelete'])
				continue;
			
			if (!isset($model->data['Attachment']))
				//throw new Exception("Attachment data missing");
				continue;
			
			//flag attachments for removal
			if (isset($model->data['Attachment'][$field])) 
			{
				
				foreach($model->data['Attachment'][$field] as $idx => $attachment) {
					$this->_flagForRemoval($model, $model->id, $field, $attachment['basename']);
					//unset($model->data['Attachment'][$field][$idx]);
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
	protected function _getAttachments(Model &$model, $valueList = '', $config) {
		
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
			$path = $this->_getFilePath($model, $file, $config);
		
			list($filename, $ext) = self::splitBasename($basename);
		
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
	protected function _getFilePath(Model &$model, $filename = null, $config) {
		if (!$filename)
			throw new InvalidArgumentException(__("AttachableBehavior: Can not get file path for empty filename"));
		
		
		return $this->_getBasePath($model, $config) . $filename;
	}
	
	protected function _getBasePath(Model &$model, $config) {
		if (!$config['baseDir'])
			throw new InvalidArgumentException(__("AttachableBehavior: Basedir can not be empty"));
		
		if (!is_dir($config['baseDir']) || !is_writeable($config['baseDir']))
			throw new InvalidArgumentException(__("AttachableBehavior: Basedir does not exist or is not writeable"));
		
		return $config['baseDir'] . $this->_replacePathTokens($model, $config['subFolder']);
	}
	
	protected function _replacePathTokens(Model &$model, $path) {
		
		$modelAlias = Inflector::underscore($model->alias);
		$modelId = ($model->id) ? $model->id : 0;
		
		return preg_replace(
			array('/\{DS\}/','/\{MODEL\}/','/\{MODELID\}/'), 
			array(DS, $modelAlias , $modelId),
			$path
		);
	}
	
	/**
	 * Flag $filepath to be removed afterDelete
	 * 
	 * @param Model $model
	 * @param string $filepath
	 * @return void
	 */
	protected function _flagForRemoval(Model &$model, $id, $field, $fileStr = null) {
		$config = $this->settings[$model->alias][$field];
		
		foreach($this->_getAttachments($model, $fileStr, $config) as $_attachment) {
			$this->_flaggedForRemoval[$model->alias][$id][$field][] = $_attachment['path'];
		}
	}
	
	/**
	 * Remove flagged files for given model
	 * 
	 * @param Model $model
	 * @return void
	 */
	protected function _removeFiles(Model &$model) {
		
		if (!isset($this->_flaggedForRemoval[$model->alias]) 
			|| !isset($this->_flaggedForRemoval[$model->alias][$model->id])
			|| empty($this->_flaggedForRemoval[$model->alias][$model->id]))
		{
			return; 
		}
		
		foreach($this->_flaggedForRemoval[$model->alias][$model->id] as $field => $filepaths) {
			
			//if 'allowOverwrite' and 'removeOnOverwrite' are ON and the file name has NOT change the file should not be deleted!
			$paths = Hash::extract($model->data, "Attachment.$field.{n}.path");
			//debug($paths);
			
			foreach ($filepaths as $idx => $filepath) {
				/*
				if (in_array($filepath, $paths)) {
					debug("do not remove path $filepath");
					unset($this->_flaggedForRemoval[$model->alias][$model->id][$field][$idx]);
					continue;
				}
				*/
				
				if (!file_exists($filepath) || !is_file($filepath)) {
					$this->log(__("Skip Delete for attachment for Model %s [%s] (File not found): %s", $model->alias, $model->id, $filepath),'debug');
					unset($this->_flaggedForRemoval[$model->alias][$model->id][$field][$idx]);
				}
				elseif (@unlink($filepath)) {
					$this->log(__("Deleted attachment for Model %s [%s]: %s", $model->alias, $model->id, $filepath),'debug');
					unset($this->_flaggedForRemoval[$model->alias][$model->id][$field][$idx]);
				} 
				else {
					$this->log(__("Failed to delete attachment for Model %s [%s]: %s", $model->alias, $model->id, $filepath),'error');
				}
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
			UPLOAD_ERR_INI_SIZE => __("Maximum ini file size exceeded (%s)",ini_get('upload_max_filesize')),
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