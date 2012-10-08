<?php
App::uses('MediaAppController','Media.Controller');
class MediaController extends MediaAppController {

	public function admin_index() {
		
		$config = Configure::read('Media');
		$this->set(compact('config'));
	}
	
}