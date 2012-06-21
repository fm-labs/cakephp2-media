<?php
App::uses('Folder','Utility');

class FileBrowserController extends MediaAppController {
	
	public $components = array('Media.FileBrowser' => array());

	public $uses = array();
	
/**
 * WWW_ROOT FileBrowser
 */	
	public function admin_index($config = 'default') {
		try {
			$this->FileBrowser->config($config);
			$this->FileBrowser->dispatch();
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
			$this->set('exception',$e);
		}
	}

/**
 * IMAGES FileBrowser
 */	
	public function admin_images() {
		$this->FileBrowser->basePath(IMAGES);
		$this->FileBrowser->baseUrl('/'.IMAGES_URL);
		$this->FileBrowser->dispatch();
	}

/**
 * ROOT FileBrowser
 */	
	public function admin_root() {
		$this->FileBrowser->basePath(ROOT);
		$this->FileBrowser->baseUrl('/');
		$this->FileBrowser->dispatch();
	}
	
	public function admin_upload() {
		
		$this->loadModel('Media.Upload');
		
		if ($this->request->data) {
			debug($this->request->data);
			$upload = $this->Upload->save($this->request->data);
			debug($upload);
			if ($upload) {
				$this->Session->setFlash(__("Upload successful"));
				debug($this->Upload->data);
			} else {
				$this->Session->setFlash(__("Upload failed"));
			}
		} else {
			$this->Session->setFlash(__("No upload started"));
		}
	}
}
?>
