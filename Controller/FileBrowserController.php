<?php
App::uses('Folder','Utility');

class FileBrowserController extends AppController {
	
	public $components = array('Media.FileBrowser' => array());
	
	public function admin_index() {
		$this->FileBrowser->read();
	}
	
	public function admin_images() {
		
		$this->FileBrowser->basePath(DH_IMAGES);
		$this->FileBrowser->baseUrl('/'.IMAGES_URL.DH_IMAGES_URL);
		$this->FileBrowser->read();
		$this->render('admin_index');
	}
	
	public function admin_root() {
		$this->FileBrowser->basePath(ROOT);
		$this->FileBrowser->baseUrl('/');
		$this->FileBrowser->read();
		$this->render('admin_index');
	}
}
?>
