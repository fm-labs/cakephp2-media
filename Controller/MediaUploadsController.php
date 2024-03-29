<?php
App::uses('MediaAppController', 'Media.Controller');
/**
 * MediaUploads Controller
 *
 * @property MediaUpload $MediaUpload
 * @property UploaderComponent $Uploader
 */
class MediaUploadsController extends MediaAppController {

	public $uses = array('Media.MediaUpload');
	
	public $components = array('Media.Uploader');
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		$this->_configureAttachment();
	}
	
	public function _configureAttachment() {
		$this->MediaUpload->configureAttachment(array(
				'file' => array(
						'multiple' => false,
						'preview' => true,
						'maxFileSize' => 8*1024*1014
				),
				'files' => array(
						'multiple' => true,
						'preview' => array(
							'small' => array('width' => 50, 'height' => 50),
							'big' => array('width' => 500, 'height' => 500),
						)
				)
		), true);
	}
	
	public function admin_test() {
		
	}
	
	public function admin_upload($id = null) {
		
		$additionalData = array(
			'id' => $id,
			'primary' => true	
		);
		
		$this->Uploader->setModel($this->MediaUpload);
		$this->Uploader->setAdditionalData($additionalData);
		if ($this->Uploader->upload(null,array(),true)) {
			$this->Session->setFlash('Upload successful');
		} else {
			$this->Session->setFlash('Upload failed');
		}
	}
	
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
