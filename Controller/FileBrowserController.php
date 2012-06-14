<?php
App::uses('Folder','Utility');

class FileBrowserController extends MediaAppController {
	
	public $components = array('Media.FileBrowser' => array());
	
	public function beforeFilter() {
		
		debug($this->passedArgs);
		
		if ($this->request->is('ajax') || (isset($this->passedArgs['iframe']) && $this->passedArgs['iframe'])) {
			$this->layout = "ajax";
			debug("hello");
		}
	}
	
	public function admin_index() {
		
		$this->FileBrowser->dispatch();
	}
	
	public function admin_images() {
		
		$this->FileBrowser->basePath(IMAGES);
		$this->FileBrowser->baseUrl('/'.IMAGES_URL);
		$this->FileBrowser->read();
		
		$this->render('admin_index');
	}
	
	public function admin_root() {
		$this->FileBrowser->basePath(ROOT);
		$this->FileBrowser->baseUrl('/');
		$this->FileBrowser->read();
		$this->render('admin_index');
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
