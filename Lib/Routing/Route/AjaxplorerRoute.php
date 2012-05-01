<?php
App::uses('CakeRoute','Routing/Route');

class AjaxplorerRoute extends CakeRoute {

	public function parse($url) {
		$parsed = parent::parse($url);
		
		if (preg_match('/.php$/i',$parsed['action'])) {
			$parsed['resource'] = $parsed['action'];
			//$parsed['resource'] = preg_replace('/.php$/i', '', $parsed['resource']);
			$parsed['action'] = 'file';
		}
		
		//debug($parsed);
		return $parsed;
	}
	
}