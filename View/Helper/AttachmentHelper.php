<?php
App::uses('AppHelper', 'View/Helper');

/**
 * 
 * @author flow
 * @property HtmlHelper $Html
 */
class AttachmentHelper extends AppHelper {

	public $helpers = array('Html');
	
	public function __construct($View, $settings = array()) {
		parent::__construct($View, $settings);
		
		$this->Html->css('/media/css/attachments',null, array('inline'=>false));
	}
	
	public function preview($field = null, $data = null, $size = 'default') {
		
		$this->setEntity($field);
		$modelKey = $this->model();
		$fieldKey = $this->field();
		
		//TODO make this configurable 
		$actionUrl = am(array(
				'plugin' => $this->request->params['plugin'],
				'controller' => $this->request->params['controller'],
				'action' => 'attachment',
		),$this->request->params['named'],$this->request->params['pass'],array('model'=>$modelKey,'field'=>$fieldKey));
		
		$editUrl = am($actionUrl, array('cmd'=>'edit'));
		$deleteUrl = am($actionUrl, array('cmd'=>'delete'));
		
		$attachments = $this->getAttachments($fieldKey, $data);
		
		$out = "";
		
		foreach($attachments as $attachment) {
			$_out = $_actions = "";
			//thumb
			$_out .= $this->Html->div('attachment-form-preview-thumb', $this->previewImage($attachment, $size));
			//name
			$_out .= $this->Html->div('attachment-form-preview-basename', 
					$this->Html->link(String::truncate($attachment['basename'], 45), array(), array('title'=>$attachment['basename']))
			);
			//actions
			$_actions .= $this->Html->link(__('Edit'), $editUrl, array('class'=>'attachment-edit'));
			$_actions .= $this->Html->link(__('Delete'), $deleteUrl, array('class'=>'attachment-delete'),__("Do you really want to delete the attachment '%s'",$attachment['basename']));
			$_out .= $this->Html->div('attachment-form-preview-actions', $_actions);
			
			//wrap inner
			$_out = $this->Html->div('attachment-form-preview', $_out);
			
			//wrap outer
			$out .= $this->Html->div('attachment-form-preview-wrap', $_out);
		}
		
		return $this->Html->div('attachment-form-container',$out . '<div style="clear:both;"></div>');
	}
	
	public function previewImage($attachment, $size = 'default') {
		if (!isset($attachment['preview']) 
			|| !isset($attachment['preview'][$size])
			|| !$attachment['preview'][$size]) 
		{
			$_extIcon =  ($attachment['ext']) ? $attachment['ext'] : 'file';
			return $this->Html->image('/media/img/icons/files/'.$_extIcon.'.png');
		}
		
		return $this->Html->image($attachment['preview'][$size]['url']);
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
}