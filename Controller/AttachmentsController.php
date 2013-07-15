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
	
	public function view() {
		
		$this->autoRender = false;
		$this->response->type('text');
		
		$model = $this->passedArgs['model'];
		$id = $this->passedArgs['id'];
		$basename = $this->passedArgs['basename'];
		
		try {
			if (!$model || !$id)
				throw new CakeException('Invalid Request');
			
			$Model =& ClassRegistry::init(Inflector::camelize($model));
			$Model->id = $id;
			
			$Model->recursive = -1;
			$data = $Model->read(null,$id);
			if (!$data)
				throw new NotFoundException();
			
			debug($data);
			
		} catch(Exception $e) {
			debug($e->getMessage());
		}
		$this->render();
	}
}