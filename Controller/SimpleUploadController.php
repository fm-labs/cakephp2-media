<?php
class SimpleUploadController extends MediaAppController {
	
	public $uses = array();
	
	function admin_index() {
		
		if ($this->request->is('post')) {
			$data = $this->request->data;
			
			/*
			$upload = new Upload();
			$upload->setUploadDir(TMP.DS.'upload'.DS);
			$upload->allowMultiple(true);
			$upload->allowMime(array('image/*'));
			$upload->allowExt(array('jpg','jpeg','png'));
			$upload->continueOnError(true); // continue uploading files, if one upload fails (only for multiple uploads)
			$upload->process($_FILES);
			*/
			debug($data);
			debug($_FILES);
		}
		
	}
	
}

/**
 * 

### SINGLE ###

array(
	'extra' => 'extravalue',
	'uploadfile' => array(
		'name' => 'brucelee.jpg',
		'type' => 'image/jpeg',
		'tmp_name' => '/tmp/phpd8vJqC',
		'error' => (int) 0,
		'size' => (int) 29356
	)
)
		
### MULTIPLE ###
			
array(
	'extra' => 'extravalue',
	'uploadfile' => array(
		(int) 0 => array(
			'name' => 'anmeldung.pdf',
			'type' => 'application/pdf',
			'tmp_name' => '/tmp/phpl1uoAf',
			'error' => (int) 0,
			'size' => (int) 88675
		),
		(int) 1 => array(
			'name' => 'brucelee.jpg',
			'type' => 'image/jpeg',
			'tmp_name' => '/tmp/phpa4U0Ku',
			'error' => (int) 0,
			'size' => (int) 29356
		)
	)
)

### $_FILES ###

array(
	'data' => array(
		'name' => array(
			'uploadfile' => array(
				(int) 0 => 'anmeldung.pdf',
				(int) 1 => 'brucelee.jpg'
			)
		),
		'type' => array(
			'uploadfile' => array(
				(int) 0 => 'application/pdf',
				(int) 1 => 'image/jpeg'
			)
		),
		'tmp_name' => array(
			'uploadfile' => array(
				(int) 0 => '/tmp/phpl1uoAf',
				(int) 1 => '/tmp/phpa4U0Ku'
			)
		),
		'error' => array(
			'uploadfile' => array(
				(int) 0 => (int) 0,
				(int) 1 => (int) 0
			)
		),
		'size' => array(
			'uploadfile' => array(
				(int) 0 => (int) 88675,
				(int) 1 => (int) 29356
			)
		)
	)
)

*/
