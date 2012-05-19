<?php
App::uses('AppHelper','View/Helper');

/**
 * FileFormHelper
 * 
 * @author Flow
 * @property HtmlHelper $Html
 * @property FormHelper $Form
 */
class FileFormHelper extends AppHelper {
	
	public $helpers = array('Html','Form','Js');
	
	public function __construct(&$View, $settings = array()) {
		parent::__construct($View,$settings);
		$this->Html->css('/media/css/fileform',null,array('inline'=>false));
	}
	
	public function imageInput($field, $params = array(), $inputParams = array(), $scriptParams = array()) {
		
		$this->Form->setEntity($field);
		$__model = $this->Form->model();
		$__field = $this->Form->field();
		$__value = $this->Form->value($field);
		

		//params
		if (!is_array($params)) {
			$params = array('href' => $params);
		} else {
			$params = array_merge(array(
				'href'=>array('controller'=>'image_browser','action'=>'select')
			),$params);
		}
		
		$out = "";
		
		//input field
		$inputParams = array_merge(array(
			'type'=>'hidden',
			'id'=>uniqid(),
			'value' => $__value,
		),$inputParams);
		$out .= $this->Form->input($field,$inputParams);
		
		//set ids
		$__id = $inputParams['id'];
		$__openerId = md5($__id);
		
		//preview
		$_preview = $this->Html->image(
			DH_IMAGES_URL . $__value,
			array('height'=>'50px','id'=>'image-input-preview')
		);
		$out .= $this->Html->div('image-input-preview',$_preview);
		unset($_preview);
		
		//info
		$_info = $this->Html->tag('span', $__value);
		$out .= $this->Html->div('image-input-info',$_info);
		unset($_info);
		
		//actions
		$_actions = $this->Html->link(__("Change"),'#',array('id'=>$__openerId,'data-target'=>$__id));
		$_actions .= $this->Html->link(__("Remove"),'#');
		$out .= $this->Html->div('image-input-actions',$_actions);
		unset($_actions);
		
		//script
		$scriptParams = array_merge(array(
			'href' => $params['href'],
			'innerWidth' => '80%',
			'innerHeight' => '80%',
			'iframe' => true
		),$scriptParams);
		$_script = $this->Js->get($__openerId)->colorbox($scriptParams);
		$out .= $this->Html->scriptBlock($this->Js->domReady($_script));
		
		//clearfix
		$out .= $this->Html->div('clearfix','');
		
		return $this->Html->div('input file-select',$out);
	}
	
}
