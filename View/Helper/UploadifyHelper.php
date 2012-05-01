<?php
class UploadifyHelper extends AppHelper {
	
	public $helpers = array('Html','Js');
	
	function __construct(&$View, $settings = array()) {
		parent::__construct($View,$settings);
		
		$settings = array_merge(array(
			'loadJquery' => false
		),$settings);
		
		if ($settings['loadJquery'] == true) {
			$this->Html->script('/media/js/jquery/jquery-1.7.1.min.js',array('inline'=>false));
		}
		$this->Html->script('/media/js/jquery.uploadify/jquery.uploadify',array('inline'=>false));
		$this->Html->css('/media/css/uploadify',array(),array('inline'=>false));
	}
}
?>