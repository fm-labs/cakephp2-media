<?php
App::uses('AppHelper', 'View/Helper');

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
		
		$attachments = $this->getAttachments($fieldKey, $data);
		
		$out = "";
		foreach($attachments as $attachment) {
			$_out = "";
			$_out .= $this->Html->div('attachment-form-preview-thumb', $this->previewImage($attachment, $size));
			$_out .= $this->Html->div('attachment-form-preview-basename', 
					$this->Html->link(String::truncate($attachment['basename'], 45), array(), array('title'=>$attachment['basename']))
			);
			
			$_out = $this->Html->div('attachment-form-preview', $_out);
			$out .= $this->Html->div('attachment-form-preview-wrap', $_out);
		}
		return $this->Html->div('attachment-form-container',$out . '<div style="clear:both;"></div>');
	}
	
	public function previewImage($attachment, $size = 'default') {
		if (!isset($attachment['preview']) 
			|| !isset($attachment['preview'][$size])
			|| !$attachment['preview'][$size]) 
		{
			return 'No preview';
			//return $this->Html->image('/media/img/attachments/default.jpg');
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