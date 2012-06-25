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
		try {
			debug(ROOT);
			$this->FileBrowser->basePath(ROOT.DS);
			$this->FileBrowser->baseUrl('/');
			$this->FileBrowser->dispatch();
		} catch(Exception $e) {
			debug($e->getMessage());
		}
	}
	
	public function admin_tmp() {
		try {
			$this->FileBrowser->basePath(TMP);
			$this->FileBrowser->baseUrl(false);
			$this->FileBrowser->dispatch();
		} catch(Exception $e) {
			debug($e->getFile().":".$e->getLine()." >". $e->getMessage());
		}
	}
	
	
	
	
	public function admin_upload() {
		
		$this->loadModel('Media.Upload');
		
		if ($this->request->data) {
			debug($this->request->data);
			$upload = $this->Upload->save($this->request->data);
			debug($upload);
			if ($upload) {
				$this->Session->setFlash(__d('media',"Upload successful"));
				debug($this->Upload->data);
			} else {
				$this->Session->setFlash(__d('media',"Upload failed"));
			}
		} else {
			$this->Session->setFlash(__d('media',"No upload started"));
		}
	}
}
?>
