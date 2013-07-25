<?php
/**
 * Attachment Helper
 * 
 * @author flowmotion
 * 
 * @property HtmlHelper $Html
 * @property FormHelper $Form
 */
App::uses('AppHelper', 'View/Helper');

class AttachmentHelper extends AppHelper {

	public $helpers = array('Html','Form');
	
	public function __construct($View, $settings = array()) {
		parent::__construct($View, $settings);
		
		$this->Html->css('/media/css/attachments',null, array('inline'=>false));
	}


	public function uploadField($fieldName, $options = array(), $uploaderConfig = array()) {
		
		$this->setEntity($fieldName);
		$modelKey = $this->model();
		$fieldKey = $this->field();

		$options = am(array(
			'holder' => $fieldName.'_upload',
			'multiple' => false,
			'type' => 'file',
			'id' => uniqid('fileinput')
		),$options);
		
		$valueHolderId = $options['id'].'_vh';
		
		//output form field
		$uploadFieldName = $options['holder'];
		unset($options['holder']);
		
		if ($options['multiple'])
			$uploadFieldName .= '.';
		
		$out = $this->Form->input($uploadFieldName, $options);
		//$out .= $this->Form->error($uploadFieldName);
		$out .= $this->Form->hidden($fieldName,array('id'=>$valueHolderId));
		$out .= $this->Form->error($fieldName);

		return $out;
	}
		
	
	/*** DEPRECATED ***/
	
	
	/**
	 * Display attachment preview for given $field
	 * 
	 * @param string $field Attachment field name
	 * @param array $data Form data
	 * @param array $options
	 * 
	 * @todo Use HtmlHelper compatible display tags for html outputs
	 */
	public function preview($field = null, $data = null, $options = array()) {
		
		$this->setEntity($field);
		$modelKey = $this->model();
		$fieldKey = $this->field();
		
		$options = am(array(
			'label' => Inflector::humanize($fieldKey),
			'size' => 'default',
			'actionEdit' => false,
			'actionDelete' => false,
		),$options);
		
		//TODO make actionUrl configurable 
		$actionUrl = am(array(
				'plugin' => $this->request->params['plugin'],
				'controller' => $this->request->params['controller'],
				'action' => 'attachment',
		),$this->request->params['named'],$this->request->params['pass'],array('model'=>$modelKey,'field'=>$fieldKey));
		
		
		$attachments = $this->getAttachments($fieldKey, $data);
		
		$label = $out = "";
		
		if ($options['label']) {
			$label = $this->Html->tag('label',$options['label']);
		}
		
		foreach($attachments as $attachment) {
			$_out = $_actions = "";
			//thumb
			$_out .= $this->Html->div('attachment-form-preview-thumb', 
					$this->previewImage($attachment, array(), $options['size']));
			//name
			$_out .= $this->Html->div('attachment-form-preview-basename', 
					$this->Html->link(String::truncate($attachment['basename'], 45), array(), array('title'=>$attachment['basename']))
			);
			//actionEdit
			if ($options['actionEdit']) {
				$editUrl = am($actionUrl, array('cmd'=>'edit'));
				$_actions .= $this->Html->link(__('Edit'), $editUrl, array('class'=>'attachment-edit'));
			}
			//actionDelete
			if ($options['actionDelete']) {
				$deleteUrl = am($actionUrl, array('cmd'=>'delete'));
				$_actions .= $this->Html->link(__('Delete'), $deleteUrl, array('class'=>'attachment-delete'),__("Do you really want to delete the attachment '%s'",$attachment['basename']));
			}
			$_out .= $this->Html->div('attachment-form-preview-actions', $_actions);
			
			//wrap inner
			$_out = $this->Html->div('attachment-form-preview', $_out);
			
			//wrap outer
			$out .= $this->Html->div('attachment-form-preview-wrap', $_out);
		}
		
		return $this->Html->div('attachment-form-container',$label.$out . '<div style="clear:both;"></div>');
	}
	
	public function previewImage($attachment, $options = array(), $size = 'default') {
		if (!isset($attachment['preview'])) {
			$_extIcon =  ($attachment['ext']) ? $attachment['ext'] : 'file';
			return $this->Html->image('/media/img/icons/files/'.$_extIcon.'.png');
		}
		
		if (isset($options['size'])) {
			$size = $options['size'];
			unset($options['size']);
		}

		return $this->Html->image($attachment['preview'][$size]['url'], $options);
	}
	
	/**
	 * Extract attachments for $field from $data
	 * 
	 * @param string $field		Field name
	 * @param array $data	Model data
	 * @param stri $ngattachmentKey		Defaults to 'Attachment'
	 * @return array	Attachment list for $field. If $field is NULL returns all attachments
	 */
	public function getAttachments($field = null, $data = null, $attachmentKey = 'Attachment') {
		if ($data === null)
			$data = $this->data;
		
		if (!isset($data[$attachmentKey]) || ($field && !isset($data[$attachmentKey][$field])))
			return array();
		
		if ($field)
			return $data[$attachmentKey][$field];
		
		return $data[$attachmentKey];
		
	}
	
	public function _uploadField($fieldName, $options = array(), $uploaderConfig = array()) {
	
		$this->setEntity($fieldName);
		$modelKey = $this->model();
		$fieldKey = $this->field();
	
		$options = am(array(
			'holder' => $fieldName.'_upload',
			'multiple' => false,
			'type' => 'file',
			'id' => uniqid('fileinput')
		),$options);
	
		$valueHolderId = $options['id'].'_vh';
	
		//output form field
		$uploadFieldName = $options['holder'];
		unset($options['holder']);
	
		if ($options['multiple'])
			$uploadFieldName .= '.';
	
		$out = $this->Form->input($uploadFieldName, $options);
		//$out .= $this->Form->error($uploadFieldName);
		$out .= $this->Form->hidden($fieldName,array('id'=>$valueHolderId));
		$out .= $this->Form->error($fieldName);
	
		if ($uploaderConfig === false)
			return $out;
	
		//build uploader script
		//TODO export to standalone function
		
		//TODO make uploaderUrl configurable
		$uploaderUrl = am(array(
				'plugin' => $this->request->params['plugin'],
				'controller' => $this->request->params['controller'],
				'action' => 'upload',
		),$this->request->params['named'],$this->request->params['pass']);
	
		$uploaderUrl = Router::url($uploaderUrl);
	
		$uploaderConfig = am(array(
				'uploadUrl' => $uploaderUrl,
				'valueHolder' => '#'.$valueHolderId,
		),$uploaderConfig);
	
		$script = 'var uploader = new UploaderUi('.json_encode($uploaderConfig).');';
		$script .= 'uploader.bindTo("#'.$options['id'].'");';
		$out .= $this->Html->scriptBlock($script);

		$this->Html->script('/media/js/uploader',array('inline'=>false));
		$this->Html->script('/media/js/uploader_ui',array('inline'=>false));
		$this->Html->css('/media/css/uploader',null, array('inline'=>false));
	
		return $out;
	}	
	
}