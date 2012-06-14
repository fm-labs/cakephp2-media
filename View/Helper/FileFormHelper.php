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
				'href'=>array('controller'=>'file_browser','action'=>'index','plugin'=>'media','iframe'=>true)
			),$params);
		}
		
		$out = "";
		
		//input field
		$inputParams = array_merge(array(
			//'type'=>'hidden',
			'type'=>'text',
			'id'=>uniqid(),
			'value' => $__value
		),$inputParams);
		
		//set ids
		$__id = $inputParams['id'];
		$__openerId = md5($__id);
		$__previewId = $__id . '-preview';
		$__previewImgId = $__previewId . '-img';
		
		$inputParams['data-preview'] = $__previewId;
		$inputParams['data-preview-img'] = $__previewImgId;
		
		//preview
		$_preview = $this->Html->image(
			DH_IMAGES_URL . $__value,
			array('class'=>'image-preview', 'id'=>$__previewImgId)
		);
		$out .= $this->Html->div('image-input-preview',$_preview,array('id'=>$__previewId));
		unset($_preview);
		
		//input
		$_input = "";
		#$_input .= $this->Html->tag('span', $__value);
		$_input .= $this->Form->input($field,$inputParams);
		
		$_actions = "";
		$_actions .= $this->Html->link(__("Change"),'#',array('class'=>'image-input-action', 'id'=>$__openerId,'data-target'=>$__id));
		$_actions .= $this->Html->link(__("Remove"),'#',array('class'=>'image-input-action'));
		$_input .= $this->Html->div('image-input-actions',$_actions);
		unset($_actions);
		
		$out .= $this->Html->div('image-input',$_input);
		unset($_input);
		
		//script
		$scriptParams = array_merge(array(
			'href' => $params['href'],
			'innerWidth' => '80%',
			'innerHeight' => '80%',
			'iframe' => true
		),$scriptParams);
		$_script = $this->Js->get('#'.$__openerId)->colorbox($scriptParams);
		$out .= $this->Html->scriptBlock($this->Js->domReady($_script));
		
		//clearfix
		$out .= $this->Html->div('ym-clearfix clearfix','');
		
		return $this->Html->div('input file-select',$out);
	}
	
}
