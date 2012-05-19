<?php
App::uses('Folder','Utility');

class FileBrowserController extends AppController {
	
	public $components = array('Media.FileBrowser' => array());
	
	public function admin_index($dir = null) {
		
		$this->FileBrowser->basePath = DH_IMAGES;
		$this->FileBrowser->read();
		
	}
}
?>
