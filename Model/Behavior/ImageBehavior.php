<?php
App::uses('Router','Routing');
App::uses('ModelBehavior','Model');
App::uses('LibPhpThumb','Media.Lib');

class ImageBehavior extends ModelBehavior {

	public $images;
	
	public function setup($model, $config = array()) {
		
		if (!isset($this->settings[$model->alias])) {
			//config
			$default = array(
				'baseDir' => '', //relative path to IMAGES
				'entity' => 'Image', //entity for adding to data
				'defaultImage' => null, //relative location of default image from baseDir
				//default settings
				//unknown setting keys will be passed to phpThumb
			);
			$this->settings[$model->alias] = array_merge($default, $config);
			
			//images
			$images = array();
			if (isset($model->images)) {
				foreach($model->images as $field => $config) {
					$images[$field] = $config;
				}
			}
			$this->images[$model->alias] = $images;
		}
	}
	
	public function beforeSave(&$model) {
		$s =& $this->settings[$model->alias];
		
		//which fields hold image paths?	
		$_fields = array_keys($this->images[$model->alias]);
		
		//check data if any field is set
		foreach($_fields as $idx => $_field) {
			//remove from field list if not set
			if (!isset($model->data[$model->alias][$_field])) {
				unset($_fields[$idx]);
			} elseif (is_array($model->data[$model->alias][$_field])) { //on upload do nothing
				unset($_fields[$idx]);
			} elseif(strlen(trim($model->data[$model->alias][$_field])) < 1) {
				unset($_fields[$idx]);
			}
		}
		
		//exiting if no fields are set
		if (empty($_fields))
			return true;
		
		//validation loop
		$_baseDir = $this->_replaceTokens($model, $s['baseDir']);
		$validated = true;
		foreach($_fields as $_field) {
			$_image = $model->data[$model->alias][$_field];
			$_imagePath = IMAGES . $_baseDir . $_image;
			if (!file_exists($_imagePath)) {
				$model->invalidate($_field, __d('media',"The file '%s' does not exist", $_image));
				$validated = false;
			}
		}
		
		return $validated;
	}
	
	private function _replaceTokens(&$model, $path = '') {
		
		foreach(array(
			'{MODELNAME}' => Inflector::underscore($model->alias),
			'{MODELID}' => $model->id
		) as $search => $replacement) {
			$path = preg_replace(sprintf('/%s/i',$search), $replacement,$path);	
		}
		
		return $path;
	}
	
	public function afterFind(&$model, $result, $primary) {
		
		//is there even a result?
		//only supporting primary == true yet

		if (!$result || !isset($result[0]) || !$primary || is_numeric(key($result[0])))
			return $result;

		//which fields hold image paths?	
		$_fields = array_keys($this->images[$model->alias]);
		
		//check first result row if any field is set
		foreach($_fields as $idx => $_field) {
			//remove from field list if not set
			if (!array_key_exists($_field, $result[0][$model->alias])) {
				unset($_fields[$idx]); 
			}
		}
		
		//exiting if no fields are set
		if (empty($_fields))
			return $result;
		
		
		//loop result	
		foreach($result as &$row) {
			foreach($_fields as $_field) {
				#debug(__("Attach %s to row %s",$_field, $row[$model->alias][$model->primaryKey]));
				$this->attachThumb($model, $row, $_field);
			}
		}	

		return $result;
	}

/**
 * Attach Thumb path and url info to data row
 * 
 * @param Model $model
 * @param array $data
 * @param string $field
 * @throws CakeException
 * @return void
 */	
	public function attachThumb(&$model, &$data, $field = 'image') {
		$s =& $this->settings[$model->alias];
		
		if (!isset($data[$model->alias][$field]) || empty($data[$model->alias][$field])) {
			
			if (!$s['defaultImage'])
				return;

			$data[$model->alias][$field] = null;	
		}
		
		if (isset($data[$model->alias][$model->primaryKey])) {
			$model->id = $data[$model->alias][$model->primaryKey];
		}
			
		//config	
		if (!isset($this->images[$model->alias][$field])) {
			if (Configure::read('debug') > 0) {
				throw new CakeException(__d('media',"ThumbBehavior::attachThumb() Thumb configuration for field '%s' is not set", $field));
			}
			$images = array(
				'default' => array(
					'w' => 100,
					'h' => 100,
					'q' => 70
				)
			);
		} else {
			$images = $this->images[$model->alias][$field];
		}
		
		//render images and return paths
		$_baseDir = $this->_replaceTokens($model, $s['baseDir']);
		$_imageData = array();
		foreach($images as $name => $config) {
			$source = $source_url = $error = $image = $url = $url_full = null;
			try {
				//image source
				$file = ($data[$model->alias][$field]) ? $_baseDir.$data[$model->alias][$field] : $s['defaultImage'];
				$source = IMAGES.$file;
				$source_url = Router::url('/',true) . IMAGES_URL . $file;
				if (!file_exists($source)) {
					throw new CakeException(__d('media',"Source file '%s' does not exist", $source));
				}
					
				//image-path
				$image = LibPhpThumb::getThumbnail($source, $config);
				
				//image-url
				$url = LibPhpThumb::getThumbnailUrlFromPath($image, false);
				$url_full = LibPhpThumb::getThumbnailUrlFromPath($image, true);
					
			} catch(Exception $e) {
				#debug($e->getMessage());
				CakeLog::write('error', __d('media',"ThumbBehavior::attachThumb() [Field '%s';Thumb '%s']: %s",$field, $name, $e->getMessage()));
				$error = $e->getMessage();
			}
			
			$_imageData[$name] = compact('source','source_url','image','url','url_full','error');
		}
		$data[$s['entity']][$field] = $_imageData;
	}
}
?>