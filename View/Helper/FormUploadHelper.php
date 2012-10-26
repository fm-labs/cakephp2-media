<?php
App::uses('AppHelper', 'View/Helper');

/**
 * 
 * @author flow
 * @property HtmlHelper $Html
 * @property FormHelper $Form
 */
class FormUploadHelper extends AppHelper {

	public $helpers = array('Html', 'Form');
	
	public function __construct($View, $settings = array()) {
		parent::__construct($View, $settings);
		
		$this->Html->script('/media/js/uploader',array('inline'=>false));
		$this->Html->script('/media/js/uploader_ui',array('inline'=>false));
		$this->Html->css('/media/css/uploader',null, array('inline'=>false));
	}
	
	public function input($fieldName, $options = array(), $uploaderConfig = array()) {

		$options = am(array(
			'holder' => $fieldName.'_upload',
			'multiple' => false,
			'type' => 'file',
			'id' => uniqid('fileinput')
		),$options);
		
		$uploaderConfig = am(array(
			'uploadUrl' => Router::url(array('action'=>'upload'))		
		),$uploaderConfig);
		
		//output form field
		$fieldName = $options['holder'];
		unset($options['holder']);
		
		if ($options['multiple'])
			$fieldName .= '.';
		$out = $this->Form->input($fieldName, $options);

		//build uploader script
		$script = 'var uploader = new UploaderUi('.json_encode($uploaderConfig).');';
		$script .= 'uploader.bindTo("#'.$options['id'].'");';
		$out .= $this->Html->scriptBlock($script);
		
		return $out;
	}
	
}