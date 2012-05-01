<?php
/**
 * Example FileManager Controller with use of Media.FileManager Component
 * 
 * @author Flow
 * @property FileManagerComponent FileManager
 */
class FileManagerController extends MediaAppController {
	
	public $components = array('Media.FileManager' => array(
		'basePath' => IMAGES, //no trailing DS
		'dir' => '/images/'
	));
	
	public function admin_index() {
		$this->setAction('admin_dir_index');
	}
	
	public function admin_dir_index($dir = null) {
		
		try {
			$this->FileManager->openDir();
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
		}
		$fileManager = $this->FileManager->toArray();
		$this->set(compact('fileManager'));
	}
	
}
?>