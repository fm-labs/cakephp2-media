<?php
App::uses('LibPhpThumb','Media.Lib');

class PhpThumbHelper extends AppHelper {

	public $helpers = array('Html');
	
	public function imageUrl($path, $params = array()) {

		if ($path[0] != "/")
			$path = IMAGES . $path;
		
		$thumbUrl = LibPhpThumb::getThumbnailUrl($path, $params);
		
		return $thumbUrl;
	}
	
	public function image($path, $options = array()) {
		
		$thumbParams = array();
		if (isset($options['width'])) {
			$thumbParams['w'] = $options['width'];	
			unset($options['width']);
		}
		if (isset($options['height'])) {
			$thumbParams['h'] = $options['height'];	
			unset($options['height']);
		}
		if (isset($options['quality'])) {
			$thumbParams['q'] = $options['quality'];
			unset($options['quality']);	
		}
		
		if (isset($options['thumb'])) {
			$thumbParams = array_merge($thumbParams, $options['thumb']);
			unset($options['thumb']);
		}
				
		$thumbUrl = $this->imageUrl($path, $thumbParams);
		#if (!$thumbUrl)
		#	return false;
			
		
		if (isset($options['url'])) {
			if($options['url'] == '{source}') {
				$options['url'] = Router::url('/',true).IMAGES_URL.$path;
			} 
			elseif($options['url'] == '{thumb}') {
				$options['url'] = Router::url('/',true).IMAGES_URL.$thumbUrl;
			}
		}
		
		return $this->Html->image($thumbUrl, $options);
	}
	
}
?>