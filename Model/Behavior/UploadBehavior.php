<?php
App::uses('File','Utility');
App::uses('Folder','Utility');

class UploadBehavior extends ModelBehavior {

/**
 * Default configuration
 * 
 * @access public
 * @var mixed
 */	
	public $config = array(
		'model' => null,
		'path' => 'app/webroot/files/:model/', //relative to root or absolute path with prepended DS
		'allowedMime' => array(), //allowed mime types. default: all
		'allowedExt' => array(), //allowed file extensions. default: all
		'fileSizeMin' => 0, //max file size in bytes. default: 2MB
		'fileSizeMax' => 2097152, //max file size in bytes. default: 2MB
		'nameInvalidRegex' => '\.\s\!\@\#\$\%\^\&\(\)\+\=\{\}\[\]\'\,\~\`\-', //bad characters
		'nameInvalidSlug' => '_', //bad charaters replacement
		'nameMaxLength' => 255,
		'allowOverwrite' => false,
		'nameUnique' => true,
		'uploadFields' => array(
			'title' => 'upload_title',
			'file' => 'upload_file'
		),
	);

/**
 * @access public
 * @see ModelBehavior::setup()
 * @return void
 */	
	public function setup(Model &$model,$config = array()) {
		//set model
		if (!isset($config['model']))
			$config['model'] = Inflector::underscore($model->alias);
		//upload config from model
		if (isset($model->upload))
			$config = array_merge($model->upload,$config);	
		//join with default config
		$this->settings[$model->alias] = Set::merge($this->config, $config);
	}
	
/**
 * Triggers upload initialization
 * 
 * @see ModelBehavior::beforeValidate()
 * @return boolean
 */	
	public function beforeValidate(Model &$model) {

		if (!$this->_initUpload(&$model))
			return false;
		
		return true;
	}

	
/**
 * Detect Upload and map data
 * 
 * @access public
 * @param Model $model
 * @return boolean
 * @todo Recursive directory creating
 */	
	public function _initUpload(Model &$model) {
		
		//check if a new file has been uploaded
		$__uploadField = $this->settings[$model->alias]['uploadFields']['file'];
		$__uploadTitle = $this->settings[$model->alias]['uploadFields']['title'];
		
		if (!isset($model->data[$model->alias][$__uploadField]))
			return false;

		//upload	
		$upload = $model->data[$model->alias][$__uploadField];
		unset($model->data[$model->alias][$__uploadField]);
		
		//uploadTitle	
		if (isset($model->data[$model->alias][$__uploadTitle])) {
			$uploadTitle = $model->data[$model->alias][$__uploadTitle];
			unset($model->data[$model->alias][$__uploadTitle]);
		} else {
			$uploadTitle = $upload['name'];
		}
		
		//refId
		$uploadRefId = (isset($model->data[$model->alias]['ref_id'])) ? $model->data[$model->alias]['ref_id'] : null;
		
		//ext
		$uploadExt = (strrpos($upload['name'], ".") > 0) ? substr($upload['name'],strrpos($upload['name'], ".")+1) : null;
		
		//path
		$uploadPath = String::insert($this->settings[$model->alias]['path'],array(
			'model' => $this->settings[$model->alias]['model'],
			#'ext' => $uploadExt,
			#'ref_id' => $uploadRefId
		));
		
		//data mapping
		$model->data[$model->alias]['model'] = $this->settings[$model->alias]['model'];
		$model->data[$model->alias]['ref_id'] = $uploadRefId;
		$model->data[$model->alias]['title'] = (strlen($uploadTitle) > 0) ? $uploadTitle : $upload['name'];
		$model->data[$model->alias]['basename'] = $upload['name'];
		$model->data[$model->alias]['mime'] = $upload['type'];
		$model->data[$model->alias]['ext'] = $uploadExt;
		$model->data[$model->alias]['size'] = $upload['size'];
		$model->data[$model->alias]['error'] = $upload['error'];
		$model->data[$model->alias]['tmp_name'] = $upload['tmp_name'];
		$model->data[$model->alias]['path'] = $uploadPath;
		unset($upload); //cleanup

		//upload error
		$uploadErrors = array(
	        UPLOAD_ERR_OK =>__("There is no error, the file uploaded successfully"),
	        UPLOAD_ERR_INI_SIZE =>__("The uploaded file exceeds the upload_max_filesize directive in php.ini"),
	        UPLOAD_ERR_FORM_SIZE =>__("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"),
	        UPLOAD_ERR_PARTIAL =>__("The uploaded file was only partially uploaded"),
	        UPLOAD_ERR_NO_FILE =>__("No file was uploaded"),
	        UPLOAD_ERR_NO_TMP_DIR =>__("Missing a temporary folder"),
	        UPLOAD_ERR_CANT_WRITE => __("Can not write to disk"),
	        UPLOAD_ERR_EXTENSION => __("File upload stopped by extension")
		);
		
		if ($model->data[$model->alias]['error'] > 0){
			$model->invalidate($__uploadField,$uploadErrors[$model->data[$model->alias]['error']]);
			return false;
		}
		
		return true;
	}
	
	
/**
 * Validate Upload Name
 * Check max filename length
 * 
 * @access public
 * @param Model $model
 * @param mixed $check
 * @return boolean
 */	
	public function validateUploadBasename(Model &$model, $check) { 
		$__uploadField = $this->settings[$model->alias]['uploadFields']['file'];
		
		foreach($check as $field => $name) {
			if (strlen($name) > $this->settings[$model->alias]['nameMaxLength']) {
				$model->invalidate($__uploadField,__("The filename can not be larger than %s characters", $this->settings[$model->alias]['nameMaxLength']));
				return false;
			}
		}
		
		return true; 
	}

/**
 * Validate file mime type
 * 
 * @param Model $model
 * @param mixed $check
 * @return boolean
 */	
	public function validateUploadMime(Model &$model, $check) { 
		$__uploadField = $this->settings[$model->alias]['uploadFields']['file'];
		
		$allowedMime = $this->settings[$model->alias]['allowedMime'];
		if (empty($allowedMime) || in_array('*',$allowedMime))
			return true;
			
		foreach($check as $field => $mime) {
			if (!in_array($mime,$allowedMime)) {
				$model->invalidate($__uploadField, __("'%s' is not a valid mime type",$mime));
				return false;
			}
		}
		
		return true; 
	}

/**
 * Validate file Extension
 * The allowed extensions are defined in the settings
 * 
 * @param Model $model
 * @param mixed $check
 * @return boolean
 */	
	public function validateUploadExtension(Model &$model, $check) { 
		
		$allowedExt = $this->settings[$model->alias]['allowedExt'];
		if (empty($allowedExt) || in_array('*',$allowedExt))
			return true;
		
		foreach($check as $field => $ext) {
			if (!in_array($ext,$allowedExt)) {
				#$model->invalidate($field, __("'%s' is not a valid file extension",$ext));
				return false;
			}
		}
		
		return true; 
	}

/**
 * Validate Upload File Size
 * 
 * @access public
 * @param Model $model
 * @param mixed $check
 * @return boolean
 */	
	public function validateUploadSizeMin(Model &$model, $check) {

		$minFileSize = $this->settings[$model->alias]['fileSizeMin'];
		
		foreach($check as $field => $size) {
			if ($size <= 0 || $size < $minFileSize) {
				$model->invalidate($field,__("File size minimum file size of %s KB expected",round($minFileSize/1024,0)));
			}
		}
		return true;
	}
	
	public function validateUploadSizeMax(Model &$model, $check) {

		$maxFileSize = $this->settings[$model->alias]['fileSizeMax'];
		
		foreach($check as $field => $size) {
			if ($size <= 0 || $size > $maxFileSize) {
				$model->invalidate($field,__("File size exceeds the maximum file size of %s KB",round($maxFileSize/1024,0)));
			}
		}
		return true;
	}

/**
 * Triggers upload process
 * 
 * @see ModelBehavior::beforeSave()
 * @param Model $model
 * @return boolean
 */	
	public function beforeSave(Model &$model) {
		
		if (!$this->upload($model))
			return false;
		
		return true;
	}
	
/**
 * Move uploaded file to target location
 * 
 * @param Model $model
 * @return boolean
 */	
	public function upload(Model &$model) {
		$__uploadField = $this->settings[$model->alias]['uploadFields']['file'];
		
		//remove bad characters from name
		$model->data[$model->alias]['basename'] = preg_replace(
			'/'.$this->settings[$model->alias]['nameInvalidRegex'].'/i', 
			$this->settings[$model->alias]['nameInvalidSlug'], 
			$model->data[$model->alias]['basename']);

		//check upload target / create unique basename / file overwritting
		$uploadPath = $this->_getAbsolutePath($model->data[$model->alias]['path']);
		$uploadFile = $model->data[$model->alias]['basename'];
		$uploadTarget = $uploadPath . $uploadFile;
		
		if (file_exists($uploadTarget) && $this->settings[$model->alias]['allowOverwrite'] === false) {
			
			if ($this->settings[$model->alias]['nameUnique'] === true) {
				$i = 0;
				$_info = pathinfo($uploadTarget);
				do {
					$_newBasename = sprintf("%s-%d",$_info['filename'],++$i);
					$_newBasename .= ($_info['extension']) ? ".".$_info['extension'] : ""; //support files without extension
					$_uniqueUploadTarget = $uploadPath . $_newBasename;
					
				} while (file_exists($_uniqueUploadTarget));
				$uploadTarget = $_uniqueUploadTarget;
				$model->data[$model->alias]['basename'] = $_newBasename;
			} else {
				$model->invalidate($__uploadField, __("A file with the same name already exists"));
				return false;
			}
		}
			
		//uploaded_file
		if (isset($model->data[$model->alias]['tmp_name']) 
			&& !is_uploaded_file($model->data[$model->alias]['tmp_name'])) {
				
			$model->invalidate($__uploadField, __("File was not uploaded properly"));
			return false;
		} elseif (isset($model->data[$model->alias]['tmp_name']) 
			&& !$this->_moveUploadedFile($model->data[$model->alias]['tmp_name'], $uploadTarget)) {

			$model->invalidate($__uploadField, __("Failed to store uploaded file"));
			return false;	
		}
		
		return true;
	}

/**
 * Move Uploaded File from temporary location to target location
 * 
 * @access private
 * @param string $from
 * @param string $to
 * @return boolean True, if writing to $to was successful
 */	
	private function _moveUploadedFile($from,$to) {
		
		$From = new File($from,false);
		$To = new File($to,true);
		$success = $To->write($From->read());
		$From->delete();
		
		return $success;
	}

/**
 * Returns absolute path of given path
 * If path is relative, the equivalent absolute path has ROOT-path prepended 
 * 
 * @param string $path
 * @return string Absolute Path
 */	
	private function _getAbsolutePath($path) {
		if ($path[0] != "/")
			$path = ROOT.DS.$path;
			
		return $path;
	}
	
	public function afterFind(&$model, $results) {
		debug($results);
		return $results;
	}
	
}
?>