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
	
	/*
	public function input($fieldName, $options = array()) {
		
		if (isset($options['type']) && $options['type'] == 'file') {
			$label = (isset($options['label'])) ? $options['label'] : Inflector::humanize($fieldName);
			if ($label)
				$label = $this->Html->tag('label', $label);
			
			return $this->Html->div('input file', $label . $this->file($fieldName, $options));
		}
		
		return $this->Form->input($fieldName, $options);
	}
	*/
	
	public function input($fieldName, $options = array(), $uploaderConfig = array()) {


		$this->setEntity($fieldName);
		$modelKey = $this->model();
		$fieldKey = $this->field();

		//TODO make this configurable
		$uploaderUrl = am(array(
			'plugin' => $this->request->params['plugin'],
			'controller' => $this->request->params['controller'],
			'action' => 'upload',
		),$this->request->params['named'],$this->request->params['pass']);
		
		/*
		$uploaderUrl = am(array(
			'plugin' => 'media',
			'controller' => 'attachments',
			'action' => 'upload',
			'model' => $modelKey,
			'field' => $fieldKey
		), $this->request->params['named'],$this->request->params['pass'] );
		*/
		$uploaderUrl = Router::url($uploaderUrl);
		
		$options = am(array(
			'holder' => $fieldName.'_upload',
			'multiple' => false,
			'type' => 'file',
			'id' => uniqid('fileinput')
		),$options);
		
		$_uploaderConfig = array();
		if (isset($options['uploader'])) {
			$_uploaderConfig = $options['uploader'];
			unset($options['uploader']);
		}
		
		$uploaderConfig = am($_uploaderConfig, array(
			'uploadUrl' => $uploaderUrl,
			'valueHolder' => '#'.$options['id'].'_vh',
		),$uploaderConfig);
		
		//output form field
		$uploadFieldName = $options['holder'];
		unset($options['holder']);
		
		if ($options['multiple'])
			$uploadFieldName .= '.';
		
		$out = $this->Form->input($uploadFieldName, $options);
		//$out .= $this->Form->error($uploadFieldName);
		$out .= $this->Form->hidden($fieldName,array('id'=>$uploaderConfig['valueHolder']));
		$out .= $this->Form->error($fieldName);

		//build uploader script
		debug($uploaderConfig);
		$script = 'var uploader = new UploaderUi('.json_encode($uploaderConfig).');';
		$script .= 'uploader.bindTo("#'.$options['id'].'");';
		$out .= $this->Html->scriptBlock($script);
		
		return $out;
	}
	
}