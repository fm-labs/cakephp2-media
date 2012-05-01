<?php
class FileUploadsController extends MediaAppController {

	public $uses = array('Media.Upload','Media.Attachment');
	
	public function admin_standalone() {
	
		if ($this->request->is('post')) {
			if ($this->Attachment->saveAll($this->request->data)) {
				$this->Session->setFlash(__("Attachment successfull"));
			} else {
				$this->Session->setFlash(__("Upload failed"));
				debug($this->Attachment->validationErrors);
			}
		}
		
		$this->render('admin_upload');
		
	}
	
	public function admin_upload() {
		
		debug($this->request->data);
		
		#$this->Upload->create();
		if ($this->Upload->saveAll($this->request->data)) {
			$this->Session->setFlash(__("Upload successfull"));
			#debug($this->Upload->Attachment->getUploads());
		} else {
			$this->Session->setFlash(__("Upload failed"));
			debug($this->Upload->validationErrors);
			debug($this->Upload->Attachment->validationErrors);
			#debug($this->Upload->Attachment->getUploadErrors());
		}
		
	}
	
}
?>