<?php
App::uses('MediaAppController','Media.Controller');

class MediaDataController extends MediaAppController {

	public $uses = array();
	
	public function beforeLayout() {
		if (isset($this->Auth)) {
			$this->Auth->allow('view');
		}
	}
	
	public function view($fileId = null) {
		$this->layout = false;
		$this->autoLayout = false;
		$this->autoRender = false;
		
		if (!$fileId)
			throw new NotFoundException(__('No file selected'));
		
		$filepath = MEDIA_DATA_DIR . $fileId;
		if (!file_exists($filepath))
			throw new NotFoundException(__('File not found'));
		
		//TODO [security] check if realpath is within the MEDIA_DATA_DIR
		//TODO [security] check for valid file-extensions!
		
		$this->response->file($filepath);
	}
}