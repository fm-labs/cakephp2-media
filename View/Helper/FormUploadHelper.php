<?php
App::uses('AppHelper', 'View/Helper');

/**
 * FormUpload Helper
 * 
 * @deprecated Use AttachmentHelper instead
 * @author flow
 * 
 * @property AttachmentHelper $Attachment
 */
class FormUploadHelper extends AppHelper {

	public $helpers = array('Media.Attachment');

	public function input($fieldName, $options = array(), $uploaderConfig = array()) {
		if (Configure::read('debug') > 0) {
			trigger_error('FormUploadHelper is DEPRECATED. Use AttachmentHelper instead',E_USER_NOTICE);
		}
		return $this->Attachment->uploadField($fieldName, $options, $uploaderConfig);
	}
	
}