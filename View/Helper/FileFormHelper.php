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
	
	private $__filebrowserUrl = array(
		'plugin' => 'media',
		'controller' => 'file_browser',
		'action' => 'index',
		'admin' => true,
		'iframe' => true,
	);
	
	public function __construct(&$View, $settings = array()) {
		parent::__construct($View,$settings);
		$this->Html->css('/media/css/fileform',null,array('inline'=>false));
	}

/**
 * Creates input field which shows the filebrowser
 * 
 * @param string $field Field entity
 * @param string|array $config FileBrowser config as string or url as array
 * @param array $inputParams Params which will be passed to the input field
 * @param array $scriptParams Params which will be passed to the colorbox javascript
 */	
	public function imageInput($field, $config = 'default', $inputParams = array(), $scriptParams = array()) {
		
		$this->Form->setEntity($field);
		$__model = $this->Form->model();
		$__field = $this->Form->field();
		$__value = $this->Form->value($field);
		
		//url
		if (is_string($config)) { //use config
			$filebrowserUrl = $this->__filebrowserUrl + array($config);
		} elseif (is_array($filebrowserUrl)) {//user-defined Router::url() compatible url -> @deprecated
			$filebrowserUrl = array_merge($this->__filebrowserUrl, $config);
			$config = 'default';
		} else {
			$filebrowserUrl = $this->__filebrowserUrl;
		}
		
		$filebrowserUrl += array('filepath'=>base64_encode($__value));
		
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
		//baseUrl has to be in IMAGES
		$_baseUrl = Configure::read('FileBrowser.'.$config.'.baseUrl');
		if (preg_match('/^(img\/)/',$_baseUrl)) {
			$_baseUrl = preg_replace('/^(img\/)/','',$_baseUrl);
			$_preview = $this->Html->image(
				$_baseUrl . $__value,
				array('class'=>'image-preview', 'id'=>$__previewImgId)
			);
			$out .= $this->Html->div('image-input-preview',$_preview,array('id'=>$__previewId));
			unset($_preview);
		} else {
			//TODO show something if image can not be accessed directly
			$out .= $this->Html->div('image-input-preview image-input-preview-empty','&nbsp;');
		}
		
		//input
		$_input = "";
		#$_input .= $this->Html->tag('span', $__value);
		$_input .= $this->Form->input($field,$inputParams);
		
		$_actions = "";
		$_actions .= $this->Html->link(__("Change"),'#',array('class'=>'image-input-action', 'id'=>$__openerId,'data-target'=>$__id));
		//$_actions .= $this->Html->link(__("Remove"),'#',array('class'=>'image-input-action'));
		$_input .= $this->Html->div('image-input-actions',$_actions);
		unset($_actions);
		
		$out .= $this->Html->div('image-input',$_input);
		unset($_input);
		
		//script
		$scriptParams = array_merge(array(
			'href' => $filebrowserUrl,
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
