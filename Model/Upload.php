<?php

class Upload extends MediaAppModel {
	
	/*
	public $actsAs = array('Media.Upload' => array(
		'bind' => array('Attachment')
	));
	*/
	
	public $useTable = 'events';
	
	public $hasMany = array(
		'Attachment' => array(
			'className' => 'Media.Attachment'
		)
	);
}
?>