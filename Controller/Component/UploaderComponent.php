<?php
App::uses('Component','Controller');

/**
 * 
 * @author flow
 */
class UploaderComponent extends Component {
	
	public $controller;
	
	public $model;
	
	public $data = array();
	
	public $additionalData = array();
	
	public function initialize($controller) {
		$this->controller = $controller;
	}
	
	public function setModel(Model $model = null) {
		
		if (!is_a($model, 'Model')) {
			throw new InvalidArgumentException('UploaderComponent::setModel(): Model expected');
		}
		
		$this->model =& $model;
		return $this;
	}
	
	public function setAdditionalData($data = array()) {
		$this->additionalData = $data;
		return $this;
	}
	
	public function setData($data) {

		if ($data === null)
			$data = $this->controller->request->data;
		
		$this->data = $data;
		return $this;
	}
	
	public function upload($data = null, $options = array(), $temporary = true) {

		$this->setData($data);
		
		//TODO convert to event
		$this->_beforeUpload($data);
		
		if (!is_object($this->model))
			throw new InvalidArgumentException('UploaderComponent::upload(): No model set');

		if (!$this->model->Behaviors->attached('Attachable'))
			throw new MissingBehaviorException(array('class' => 'AttachableBehavior'));
		
		$result = $this->_upload($temporary);

		//TODO convert to event
		$this->_afterUpload($result);
		
		return $result;
	}
	
	protected function _upload($temporary = true) {
		
		if (!$this->data)
			return false;
		
		$data = array($this->model->alias => am($this->data[$this->model->alias], $this->additionalData));
		
		$this->model->create();
		
		if ($temporary) {
			if($this->model->attachTemporary($data)) {
				$result = $this->model->data;
			} else {
				$result = false;
			}
		} else {
			$result = $this->model->save($data);
		}
		
		return $result;
	}
	
	protected function _beforeUpload($uploadData) {

		if ($this->controller->request->is('ajax')) {
			$this->controller->layout = null;
			$this->controller->viewClass = 'Json';
		}
		
	}
	
	protected function _afterUpload($uploadResult) {
		
		
		if ($uploadResult) {
			//$this->controller->response->statusCode(200);
			$data = $uploadResult;
			$data = Hash::remove($data, 'Attachment.{s}.{n}.tmp_name');
			$data = Hash::remove($data, 'Attachment.{s}.{n}.path');
			
			$response = array(
				'success' => true,
				'message' => __('Upload was success'),
				'data' => $data[$this->model->alias],
				'files' => $data['Attachment']
			);
		} else {
			//$this->controller->response->statusCode(400);
			$validationErrors = $this->model->validationErrors;
			$response = array(
				'success' => false,
				'message' => __('Upload failed'),
				'data' => $validationErrors
			);
		}
		$this->controller->set(compact('response'));
		
		if ($this->controller->request->is('ajax')) {
			//$this->controller->set('_serialize',array('response'));
			$this->controller->set('_serialize','response');
		}
	}
	
}