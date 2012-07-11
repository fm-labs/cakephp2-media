<?php
App::uses('Router','Routing');
App::uses('ModelBehavior','Model');
App::uses('LibPhpThumb','Media.Lib');

class ThumbBehavior extends ModelBehavior {

	public $thumbs;
	
	public function setup($model, $config = array()) {
		
		if (!isset($this->settings[$model->alias])) {
			//config
			$default = array(
				'baseDir' => '', //relative path to IMAGES
				'entity' => 'Thumb', //entity for adding to data
				'defaultImage' => null, //relative location of default image from baseDir
				//default settings
				//unknown setting keys will be passed to phpThumb
			);
			$this->settings[$model->alias] = array_merge($default, $config);
			
			//thumbs
			$thumbs = array();
			if (isset($model->thumbs)) {
				foreach($model->thumbs as $field => $config) {
					$thumbs[$field] = $config;
				}
			}
			$this->thumbs[$model->alias] = $thumbs;
		}
	}
	
	public function beforeSave(&$model) {
		$s =& $this->settings[$model->alias];
		
		//which fields hold image paths?	
		$_fields = array_keys($this->thumbs[$model->alias]);
		
		//check data if any field is set
		foreach($_fields as $idx => $_field) {
			//remove from field list if not set
			if (!isset($model->data[$model->alias][$_field])) {
				unset($_fields[$idx]); 
			} elseif(!trim($model->data[$model->alias][$_field])) {
				unset($_fields[$idx]);
			}
		}
		
		//exiting if no fields are set
		if (empty($_fields))
			return true;
		
		//validation loop
		$validated = true;
		foreach($_fields as $_field) {
			$_image = $model->data[$model->alias][$_field];
			$_imagePath = IMAGES . $s['baseDir'] . $_image;
			if (!file_exists($_imagePath)) {
				$model->invalidate($_field, __d('media',"The file '%s' does not exist", $_image));
				$validated = false;
			}
		}
		
		return $validated;
	}
	
	public function afterFind(&$model, $result, $primary) {
		
		//is there even a result?
		//only supporting primary == true yet
		if (!$result || !isset($result[0]) || !$primary)
			return $result;

		//which fields hold image paths?	
		$_fields = array_keys($this->thumbs[$model->alias]);
		
		//check first result row if any field is set
		foreach($_fields as $idx => $_field) {
			//remove from field list if not set
			if (!isset($result[0][$model->alias][$_field])) {
				unset($_fields[$idx]); 
			}
		}
		
		//exiting if no fields are set
		if (empty($_fields))
			return $result;
		
		//loop result	
		foreach($result as &$row) {
			foreach($_fields as $_field) {
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
		
		if (!isset($data[$model->alias][$field]))
			return;

			
		if (empty($data[$model->alias][$field])) {
			if (!$s['defaultImage'])
				return;
				
			$data[$model->alias][$field] = $s['defaultImage'];	
		}
			
		//config	
		if (!isset($this->thumbs[$model->alias][$field])) {
			if (Configure::read('debug') > 0) {
				throw new CakeException(__d('media',"ThumbBehavior::attachThumb() Thumb configuration for field '%s' is not set"));
			}
			$thumbs = array(
				'default' => array(
					'w' => 100,
					'h' => 100,
					'q' => 70
				)
			);
		} else {
			$thumbs = $this->thumbs[$model->alias][$field];
		}
		
		//render thumbs and return paths
		$_thumbData = array();
		foreach($thumbs as $name => $config) {
			$source = $source_url = $error = $thumb = $url = $url_full = null;
			try {
				//image source
				$file = $data[$model->alias][$field];
				if (!file_exists(IMAGES . $s['baseDir'].$file)) {
					if ($s['defaultImage']) //use default image if source not found
						$file = $s['defaultImage'];
					else
						throw new CakeException(__d('media',"Source file '%s' does not exist", $source));
				}
				$source = IMAGES.$s['baseDir'].$file;
				$source_url = Router::url('/',true) . IMAGES_URL . $s['baseDir'] . $file;
					
				//thumb-path
				$thumb = LibPhpThumb::getThumbnail($source, $config);
				
				//thumb-url
				$url = LibPhpThumb::getThumbnailUrlFromPath($thumb, false);
				$url_full = LibPhpThumb::getThumbnailUrlFromPath($thumb, true);
					
			} catch(Exception $e) {
				debug($e->getMessage());
				CakeLog::write('error', __d('media',"ThumbBehavior::attachThumb() [Field '%s';Thumb '%s']: %s",$field, $name, $e->getMessage()));
				$error = $e->getMessage();
			}
			
			$_thumbData[$name] = compact('source','source_url','thumb','url','url_full','error');
		}
		$data[$s['entity']][$field] = $_thumbData;
	}
}
?>