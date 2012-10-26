<?php
App::uses('FormHelper', 'View/Helper');

class FormUploadHelper extends FormHelper {

	public function __construct($View, $settings = array()) {
		parent::__construct($View, $settings);
		
		$this->Html->script('/media/js/uploader',array('inline'=>false));
		$this->Html->script('/media/js/uploader_ui',array('inline'=>false));
		$this->Html->css('/media/css/uploader',null, array('inline'=>false));
	}
	
	public function input($field, $options = array(), $uploaderConfig = array()) {

		$options = am(array(
			'multiple' => false,
			'type' => 'file',
			'id' => uniqid('fileinput')
		),$options);
		
		if (substr($field, -1) == '.') {
			$options['multiple'] = 'multiple';
		}
		
		$uploaderConfig = am(array(
			'uploadUrl' => Router::url(array('action'=>'upload'))		
		),$uploaderConfig);
		
		$out = parent::input($field, $options);

		$script = 'var uploader = new UploaderUi('.json_encode($uploaderConfig).');';
		//$script .= 'uploader.init('.json_encode($uploaderConfig).');';
		$script .= 'uploader.bindTo("#'.$options['id'].'");';
		
		$out .= $this->Html->scriptBlock($script);
		
		return $out;
	}
	
}