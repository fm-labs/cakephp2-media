<?php
App::uses('MediaAppController','Media.Controller');
App::uses('Folder','Utility');
App::uses('File','Utility');

class FileBrowserController extends MediaAppController {
	
	public $components = array('Media.FileBrowser', 'Media.FileTreeConnector','Media.Uploadify');
	
	function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('admin_upload');
		$this->Uploadify->uploadPath($this->basePath);
		
		$this->FileBrowser->basePath(WWW_ROOT);
			
		if ($this->request->is('ajax')) {
			$this->layout = "empty";
		}
	}

	public function admin_index() {
		$this->setAction('admin_browse');
	}
	
	public function admin_browse() {
		$this->FileBrowser->start();
	}
	
	public function admin_file() {
		$this->setAction('admin_preview');
	}
	
	public function admin_preview($filepathEncoded = null) {
		
		$basepath = $this->basePath;
		
		$filepathEncoded = (isset($this->passedArgs['file'])) ? $this->passedArgs['file'] : $filepathEncoded;
		$filepathDecoded = base64_decode($filepathEncoded);
		$filepathFull = $basepath.$filepathDecoded;
		$File = new File($filepathFull,false);
		
		$filePath = $filepathDecoded;
		$this->baseDir = substr($filePath,0,strrpos($filePath, '/')+1);	
		$this->set(compact('File','filePath'));
	}	
	
	
	public function admin_connect($dir = '/') {
		
		if ($this->request->is('post')) {
			$dir = $this->data['dir'];
		}
		if ($this->request->is('ajax')) {
			$this->response->type('html');
			$this->layout = "empty";
		}
		
		$this->FileTreeConnector->basePath($this->basePath);
		$this->FileTreeConnector->root($dir);
		
		$contents = $this->FileTreeConnector->read();
		$this->set(compact('contents','dir'));
	}	

	public function admin_upload_form() {
		
		$this->layout = "uploadify_iframe";
	}
	
	public function admin_upload() {
		
		$this->Uploadify->upload();
		$this->Uploadify->respond();
	}	
	
}
?>