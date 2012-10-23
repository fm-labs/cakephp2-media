<?php
App::uses('MediaAppModel', 'Media.Model');

class UploadTest extends MediaAppModel {

	public $actsAs = array('Media.Attachable');
	
	public $attachments = array(
		'file' => array(
			'dir' => IMAGES		
		)	
	);
}