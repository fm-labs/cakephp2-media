<?php
App::uses('ModelBehavior', 'Model');
App::uses('MediaUploader', 'Media.Lib');
App::uses('MediaTools', 'Media.Lib');
App::uses('Folder', 'Utility');
#App::uses('Cache','Cache');
##App::uses('String', 'Utility');
#App::uses('File', 'Utility');
#App::uses('Router', 'Routing');
#App::uses('LibPhpThumb','Media.Lib');

class AttachableBehavior extends ModelBehavior {
	
	/**
	 * Default field configuration
	 * 
	 * @var array
	 */
	public $defaultConfig = array(
		'enabled' => true, // If TRUE attachments get auto attached afterFind
		'uploadField' => null, // fieldname which holds file upload. Defaults to FIELDNAME_upload.
		#'uploadNameField' => null, // fieldname which holds target file name. Defaults to FIELDNAME_name.
		'baseDir' => MEDIA_UPLOAD_DIR,
		'subFolder' => '',
		'multiple' => false, // If FALSE, only one attachment will be stored. On edit file gets overwritten automatically
		#'append' => true, // If 'multiple' is TRUE, 'append' (if TRUE) appends files on edit, otherwise files get overwritten/deleted
		'removeOnDelete' => true, //remove file if row gets deleted
		'removeOnOverwrite' => true, //remove file if file has been replaced
		#'minFileSize' => 0,
		#'maxFileSize' => 2097152, //2MB
		'allowEmpty' => true, //Allow field to be empty
		#'allowOverwrite' => false,
		#'allowedMimeType' => '*', //"*" for all or array('image/*,text/plain).
		#'allowedFileExtension' => '*', //"*" for all or array('jpg','jpeg')
		#'hashFilename' => false,
		#'slug' => '_',
	);	
	
	/**
	 * Attachments flagged for removal
	 * 
	 * @var array
	 */
	protected $_flaggedForRemoval = array();
	
	/**
	 * Runtime configuration. Overrides configuration for next operation.
	 * 
	 * @var array
	 */
	protected $_runtime = array();
	
	public function __construct() {
		parent::__construct();
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
	
		if ($reset || !$this->_getConfig($model, $field)) {
			$config = am($this->defaultConfig, array(
				'uploadField'=>$field.'_upload',
				#'uploadNameField'=>$field.'_name'
			), $config);
	
			/*
			if (!$config['baseDir'])
				throw new InvalidArgumentException(__("AttachableBehavior: Basedir can not be empty"));
				
			if (!is_dir($config['baseDir']) || !is_writeable($config['baseDir']))
				throw new InvalidArgumentException(__("AttachableBehavior: Basedir does not exist or is not writeable"));
			*/
		} else {
			$config = am($this->settings[$model->alias][$field], $config);
		}
		//TODO check if directories exist and are writeable
	
		$this->settings[$model->alias][$field] = $config;
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
	
	protected function _getConfig(Model &$model, $field = null, $includeRuntimeConfig = true) {
		if ($field === null)
			return $this->settings[$model->alias];
	
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
	
	/**
	 * (non-PHPdoc)
	 * @see ModelBehavior::afterValidate()
	 */
	public function afterValidate(Model $model) {
		#debug($model->data);
		$this->_validateUpload($model);
		#debug($model->data);
		
		return true;
	}
	

	/**
	 * Upload temporary
	 *
	 * @param Model $model
	 * @param mixed $options Overrides default behavior settings
	 * @return boolean
	 */
	protected function _validateUpload(Model &$model) {
	
		if (!$model->data)
			return false;
	
		/*
		if ($options) {
			$this->_setRuntimeConfig($model, $options);
		}
		*/
	
		//check if any field is set
		foreach($this->_getFields($model) as $field) {
			$config = $this->_getConfig($model, $field, true);
	
			//detect upload
			$_uploads = array();
			if (isset($model->data[$model->alias][$config['uploadField']])) {
					
				$value = $model->data[$model->alias][$config['uploadField']];
	
				if (is_array($value)) {
					$uploadData = $value;
					if (!isset($uploadData[0])) {
						$uploadData = array($uploadData);
					}
	
					foreach($uploadData as $idx => $upload) {
						try {
							//no upload
							if ($upload['error'] == UPLOAD_ERR_NO_FILE && $config['allowEmpty']) {
								unset($uploadData[$idx]);
								continue;
							}
	
							//check upload and upload temporary
							$_upload = $this->_upload($model, $upload, $config);
							$_uploads[] = $_upload;
	
						} catch(Exception $e) {
							debug($e);
							$model->invalidate($field, $upload['name'].': '.$e->getMessage());
							continue;
						}
					}
	
					//upload field was set, but no files submitted
					if (empty($_uploads)) {
						unset($model->data[$model->alias][$config['uploadField']]);
						continue;
					}
	
					// reset upload field
					$model->data[$model->alias][$config['uploadField']] = '';
						
					// set attachment data
					$model->data['AttachmentUpload'][$field] = $_uploads;
						
					// set attachment string
					// $attachmentsString = join(',',Set::extract('/basename', $attachmentData));
					// $model->data[$model->alias][$field] = $attachmentsString;
						
				} else {
					//ignore non array values
				}
	
			}
			else {
				//upload field not set
				continue;
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
	protected function _upload(Model &$model, $upload, $config) {
	
		#debug($config);
		#debug($upload);
	
		$Uploader = new MediaUploader($upload);
		//$Uploader->setUploadDir($attachmentUploadDir);
	
		return $Uploader->upload();
	}

	static public function getPath(Model &$model, $config) {
		return $config['baseDir'] . self::replacePathTokens($model, $config['subFolder']);
	}
	
	static public function replacePathTokens(Model &$model, $path) {
	
		$modelAlias = Inflector::underscore($model->alias);
		$modelId = ($model->id) ? $model->id : 0;
	
		return preg_replace(
				array('/\{DS\}/','/\{MODEL\}/','/\{MODELID\}/'),
				array(DS, $modelAlias , $modelId),
				$path
		);
	}
		
	public function afterSave(Model $model, $created) {
		
		$this->_storeUpload($model);
	}
	
	protected function _storeUpload($model) {

		#debug("store upload");
		#debug($model->data);
		
		if (isset($model->data['AttachmentUpload'])) {
			$clone = clone $model;
			
			foreach($model->data['AttachmentUpload'] as $field => $uploads) {
				$config = $this->_getConfig($model, $field);
				
				$attachmentUploadDir = self::getPath($model, $config);
				#debug($attachmentUploadDir);
				
				if (!is_dir($attachmentUploadDir)) {
					$Folder = new Folder($attachmentUploadDir, true, 0777);
				}
				
				$attachments = array();
				foreach($uploads as $upload) {

					// split basename
					list($filename,$ext, $dotExt) = MediaTools::splitBasename(trim($upload['name']));
					
					// filename
					$filename = Inflector::slug($filename,'_');
					$filename = uniqid($filename.'_');
					
					$basename = $filename.$dotExt;
					
					$attachmentTarget = $attachmentUploadDir . $basename;
					if (!copy($upload['path'], $attachmentTarget)) {
						debug("Failed to copy upload to $attachmentTarget");
						continue;
					}
					elseif (!unlink($upload['path'])) {
						debug("Failed to delete uploaded file " . $upload['path']);
						continue;
					}
					
					$attachment = array(
						'filename' => $filename,
						'basename' => $basename,
						'path' => $attachmentTarget,
						'ext' => $ext,
						'dotExt' => $dotExt	
					);
					$attachments[] = $attachment;
				}
				
				if ($attachments) {
					
					$attachmentData = ($config['multiple']) ? $attachments : $attachments[0];
					$model->data['Attachment'][$field] = $attachmentData;
					
					$attachmentString = join(',',Set::extract('/basename', $attachmentData));
					$model->data[$model->alias][$field] = $attachmentString;
					
					$clone->id = $model->id;
					$clone->saveField($field, $attachmentString);
				}
			}	
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ModelBehavior::beforeFind()
	 */
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
			
			$clone = clone $model;
			
			foreach($results as &$result) {
				
				if (!isset($result[$model->alias]))
					continue; 
				
				//TODO read attachment cache for result
				
				//check if any field is set
				foreach($this->_getFields($model) as $field) {
					
					$config = $this->_getConfig($model, $field, true);
					
					if (!$config['enabled'] || !array_key_exists($field, $result[$model->alias]))
						continue;
					
					if (strlen($result[$model->alias][$field]) > 0) {
						
						$clone->id = $result[$model->alias][$model->primaryKey];
						$attachments = $this->_parseAttachments($clone, $result[$model->alias][$field], $config);
						
					}
					/* 
					elseif ($config['defaultImage']) {
						$defaultImagePath = $config['defaultImage'];
						list($filename, $ext) = self::splitBasename(basename($defaultImagePath));
						$attachments = array(0 => array(
							'path' => $defaultImagePath,
							'basename' => basename($config['defaultImage']),
							'filename' => $filename,
							'ext' => $ext
						));
					}
					*/ 
					else {
						continue;
					}
					
					//attach preview
					#foreach($attachments as &$attachment)
					#	$attachment = $this->_attachPreview($attachment, $config);
					
					$result['Attachment'][$field] = ($config['multiple']) ? $attachments : $attachments[0];
				}
				
				//TODO write attachment cache
			}
			
			unset($clone);
		} else {
			//TODO afterFind non-primary results
		}
		
		return $results;
	}
	
	/**
	 * Convert field values to Attachment data set
	 *
	 * @param string|array $value Array or comma-separated string
	 * @param array $config Field config
	 * @return array
	 */
	protected function _parseAttachments(Model &$model, $basenames = array(), $config) {
	
		if (is_string($basenames))
			$basenames = explode(',',$basenames);
	
		$attachments = array();
		foreach((array)$basenames as $basename) {
			if (strlen(trim($basename)) == 0)
				continue;
				
			$path = self::getPath($model, $config) . $basename;
			list($filename, $ext, $dotExt) = MediaTools::splitBasename($basename);
	
			$attachment = compact('basename','filename','path','ext','dotExt');
			array_push($attachments, $attachment);
		}
	
		return $attachments;
	}	
	
	
	/**
	 * (non-PHPdoc)
	 * @see ModelBehavior::onError()
	 * @todo Implement onError() method
	 */
	public function onError($model, $error) {
		$this->log($error, LOG_ERR);
	}
	

	/**
	 * Check if attached files should be removed and flag them.
	 * After table row deletion was successful, the attached files will be removed.
	 *
	 * @see ModelBehavior::beforeDelete()
	 */
	public function beforeDelete($model) {
	
		$fields = array_keys($this->settings[$model->alias]);
		$readFields = am(array($model->primaryKey), $fields);
	
		$model->read($readFields,$model->id);
		
		/*
		if (!isset($model->data['Attachment'])) {
			return true;
		}
		*/
	
		foreach($fields as $field) {
			$config = $this->_getConfig($model, $field, false);
				
			//do not remove files
			if (!$config['removeOnDelete'])
				continue;
				
			//flag attachments for removal
			if (isset($model->data['Attachment'][$field]))
			{
				if ($config['multiple']) {
					foreach($model->data['Attachment'][$field] as $idx => $attachment) {
						$this->_flagForRemoval($model, $model->id, $field, $attachment['path']);
					}
				} else {
					$this->_flagForRemoval($model, $model->id, $field, $model->data['Attachment'][$field]['path']);
				}
			}
		}
	
		return true;
	}
	
	/**
	 * Flag $filepath to be removed afterDelete
	 *
	 * @param Model $model Model instance
	 * @param int $id Model Id
	 * @param string $field Model field name
	 * @param string $path Attachment path
	 * @return void
	 */
	protected function _flagForRemoval(Model &$model, $id, $field, $path) {
		// $config = $this->settings[$model->alias][$field];
		$this->_flaggedForRemoval[$model->alias][$id][$field][] = $path;
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
	
	public function log($msg) {
		#debug($msg);
		parent::log($msg);
	}
	
}