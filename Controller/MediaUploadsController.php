<?php
App::uses('MediaAppController', 'Media.Controller');
/**
 * MediaUploads Controller
 *
 * @property MediaUpload $MediaUpload
 */
class MediaUploadsController extends MediaAppController {

/**
 * admin_index method
 *
 * @return void
 */
	public function admin_index() {
		$this->MediaUpload->recursive = 0;
		$mediaUploads = $this->paginate();
		$this->set('mediaUploads', $mediaUploads);
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		$this->MediaUpload->id = $id;
		if (!$this->MediaUpload->exists()) {
			throw new NotFoundException(__('Invalid media upload'));
		}
		$this->set('mediaUpload', $this->MediaUpload->read(null, $id));
	}


	/**
	 * admin_add method
	 *
	 * @return void
	 */
	public function admin_add_html5() {
		
		if ($this->request->is('post')) {
			debug($this->data);
			
			$this->MediaUpload->create();
			if ($this->MediaUpload->save($this->request->data)) {
				$this->Session->setFlash(__('The media upload has been saved'));
				//$this->redirect(array('action' => 'index'));
			} else {
				debug($this->MediaUpload->validationErrors);
				$this->Session->setFlash(__('The media upload could not be saved. Please, try again.'));
			}
		}
	}	
	
	public function admin_upload_html5() {
		$this->layout = null;
		$this->viewClass = 'Json';
		
		$this->set('response',array('data'=>$this->request->data));
		$this->set('_serialize',array('response'));
	}
	
/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {

			$this->MediaUpload->create();
			if ($this->MediaUpload->save($this->request->data)) {
				$this->Session->setFlash(__('The media upload has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The media upload could not be saved. Please, try again.'));
				$this->request->data = $this->MediaUpload->data;
			}
		}
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		$this->MediaUpload->id = $id;
		if (!$this->MediaUpload->exists()) {
			throw new NotFoundException(__('Invalid media upload'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->MediaUpload->save($this->request->data)) {
				$this->Session->setFlash(__('The media upload has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The media upload could not be saved. Please, try again.'));
				debug($this->MediaUpload->validationErrors);
			}
			$this->request->data = $this->MediaUpload->data;
		} else {
			$this->request->data = $this->MediaUpload->read(null, $id);
		}
	}

/**
 * admin_delete method
 *
 * @throws MethodNotAllowedException
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->MediaUpload->id = $id;
		if (!$this->MediaUpload->exists()) {
			throw new NotFoundException(__('Invalid media upload'));
		}
		if ($this->MediaUpload->delete()) {
			$this->Session->setFlash(__('Media upload deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Media upload was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
