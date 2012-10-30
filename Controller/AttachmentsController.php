<?php
App::uses('MediaAppController','Media.Controller');

class AttachmentsController extends MediaAppController {

	public $uses = array();
	
	public function admin_upload() {

		//if ($this->request->is('ajax')) {
			$this->layout = null;
			$this->viewClass = 'Json';
			
			$this->set('response',array('data'=>$this->request->data));
			$this->set('_serialize',array('response'));
		//}
	}
}