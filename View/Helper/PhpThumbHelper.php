<?php
App::uses('LibPhpThumb','Media.Lib');

class PhpThumbHelper extends AppHelper {

	public $helpers = array('Html');
	
	public function image($path, $options = array()) {

		if ($path[0] != "/")
			$path = IMAGES . $path;
		
		$thumbParams = array();
		if (isset($options['width'])) {
			$thumbParams['w'] = $options['width'];	
		}
		if (isset($options['height'])) {
			$thumbParams['h'] = $options['height'];	
		}
		if (isset($options['quality'])) {
			$thumbParams['q'] = $options['quality'];
			unset($options['quality']);	
		}
		
		if (isset($options['thumb'])) {
			$thumbParams = array_merge($thumbParams, $options['thumb']);
			unset($options['thumb']);
		}
		
		$thumb = LibPhpThumb::getThumbnailUrl($path, $thumbParams);
		
	}
	
}
?>