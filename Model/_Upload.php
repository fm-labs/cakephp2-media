<?php

class Upload extends MediaAppModel {
	
	public $actsAs = array('Media.MeioUpload' => array(
		'file1' => array(
			'useTable' => false,
			//'uploadName' => 'filename',
			'dir' => 'test',
			'maxSize' => 200,
 		)
	));
	
	public $useTable = false;
	
}
?>