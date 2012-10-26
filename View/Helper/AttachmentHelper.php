<?php
App::uses('AppHelper', 'View/Helper');

class AttachmentHelper extends AppHelper {

	public $helpers = array('Html');
	
	public function preview($field = null, $data = null) {
		
		$this->setEntity($field);
		
		$modelKey = $this->model();
		$fieldKey = $this->field();
		
		$attachments = $this->getAttachments($fieldKey, $data);
		
		$out = "<ul>";
		foreach($attachments as $attachment) {
			$data = $attachment;
			unset($data['path']);
			
			$out .= $this->Html->tag('li', '<strong>'.$attachment['basename'].'</strong>', array('data' => $data));
		}
		$out .= "</ul>";
		return $out;
	}
	
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