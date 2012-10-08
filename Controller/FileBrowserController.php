<?php
App::uses('Folder','Utility');
App::uses('FileBrowser','Media.FileSystem');

/**
 * 
 * @author flow
 * @property FileBrowser $FileBrowser
 */
class FileBrowserController extends MediaAppController {
	
	#public $components = array('Media.FileBrowser' => array());

	public $uses = array();

	public $FileBrowser;
	
	protected $_config = 'default';
	protected $_args = array();
	protected $_resumed = false;
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		$this->layout = "Media.filebrowser";
	
		if (isset($this->passedArgs['config'])) {
			if ($this->Session->check('Media.FileBrowser.'.$this->passedArgs['config'])) {
				$this->FileBrowser = $this->Session->read('Media.FileBrowser.'.$this->passedArgs['config']);
				//$this->Session->write('Media.FileBrowser.'.$this->passedArgs['config'].'.resumed', true);
				$this->_resumed = true;
				$this->_config = $this->passedArgs['config'];
				$this->Session->setFlash(__('FileBrowser instance with config %s resumed',$this->_config));
			}
		} elseif ($this->action != "admin_index") {
			throw new CakeException(__('No filebrowser config provider'));
		}
		
		$this->dir = $this->request->params['pass'];
		$this->file = null;
		
		if (isset($this->passedArgs['file']))
			$this->file = $this->passedArgs['file'];
		
	}

	/**
	 * @see Component::beforeRender()
	 */
	public function beforeRender() {
		$this->set('fileBrowserConfig',$this->_config);
		if (is_object($this->FileBrowser)) {
			$this->set('fileBrowser',$this->FileBrowser->toArray());
			$this->Session->write('Media.FileBrowser.'.$this->_config,$this->FileBrowser);
		}
	}
	
	public function beforeRedirect($url, $status = null, $exit = false) {
		
		if (is_array($url)) {
			$url['config'] = $this->_config;
		}
		
		return compact('url','status','exit');
	}
	
	public function admin_index($config = null) {
		
		if (!$this->FileBrowser) {
			
			if (isset($this->passedArgs['config']))
				$config = $this->passedArgs['config'];
			
			if (!$config)
				throw new CakeException(__('No Config provided'));
			
			$this->_config = $config;
	
			$this->FileBrowser = new FileBrowser($this->_config);
			$this->Session->setFlash(__('FileBrowser instance with config %s started',$this->_config));
		}
	}
	
	public function admin_open() {
		$this->FileBrowser->set($this->dir, null);
		$this->render('admin_index');
	}
	
	public function admin_parent() {
		$this->FileBrowser->set('..', null);
		$this->redirect(array('action'=>'index'));
	}
	
	public function admin_reset($config) {
		$this->_config = $config;
		$this->Session->delete('Media.FileBrowser');
		$this->redirect(array('action'=>'index'));
	}
	
	public function admin_view() {
		$this->FileBrowser->set($this->dir, $this->file);
		$data = $this->FileBrowser->readFile();
		$this->set(compact('data'));
	}
	
	public function admin_delete() {

		$this->FileBrowser->setFile($this->file);
		if ($this->FileBrowser->deleteFile()) {
			$this->Session->setFlash(__('File %s has been deleted',$this->file));
		} else {
			$this->Session->setFlash(__('File %s could not be deleted',$this->file));
		}
		$this->redirect(array('action'=>'index'));
	}
	
	public function admin_copy() {

		$this->FileBrowser->setFile($this->file);
		if ($this->FileBrowser->copyFile()) {
			$this->Session->setFlash(__('File %s has been copied',$this->file));
		} else {
			$this->Session->setFlash(__('File %s could not be copied',$this->file));
		}
		$this->redirect(array('action'=>'index'));
	}
	
	public function admin_rename() {
		
		$this->FileBrowser->setFile($this->file);
		
		if ($this->request->is('post')) {
			$fileNew = $this->request->data['FileBrowser']['file_new'];
			try {
				$this->FileBrowser->renameFile($fileNew);
				$this->Session->setFlash(__('File %s has been renamed to %s',$this->file, $fileNew));
				$this->redirect(array('action'=>'index','file'=>$fileNew)+$this->FileBrowser->getPath());
			} catch(Exception $e) {
				$this->Session->setFlash($e->getMessage());
			}
		}
		$this->render('admin_file_rename');
	}
	
/**
 * IMAGES FileBrowser
 */	
	public function admin_images() {
		$this->FileBrowser->basePath(IMAGES);
		$this->FileBrowser->baseUrl('/'.IMAGES_URL);
		$this->FileBrowser->dispatch();
	}

/**
 * ROOT FileBrowser
 */	
	public function admin_root() {
		try {
			debug(ROOT);
			$this->FileBrowser->basePath(ROOT.DS);
			$this->FileBrowser->baseUrl('/');
			$this->FileBrowser->dispatch();
		} catch(Exception $e) {
			debug($e->getMessage());
		}
	}
	
	public function admin_tmp() {
		try {
			$this->FileBrowser->basePath(TMP);
			$this->FileBrowser->baseUrl(false);
			$this->FileBrowser->dispatch();
		} catch(Exception $e) {
			debug($e->getFile().":".$e->getLine()." >". $e->getMessage());
		}
	}
	
	
	
	
	public function admin_upload() {
		
		$this->loadModel('Media.Upload');
		
		if ($this->request->data) {
			debug($this->request->data);
			$upload = $this->Upload->save($this->request->data);
			debug($upload);
			if ($upload) {
				$this->Session->setFlash(__d('media',"Upload successful"));
				debug($this->Upload->data);
			} else {
				$this->Session->setFlash(__d('media',"Upload failed"));
			}
		} else {
			$this->Session->setFlash(__d('media',"No upload started"));
		}
	}
}
?>
