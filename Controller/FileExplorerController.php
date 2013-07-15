<?php
App::uses('MediaAppController','Media.Controller');
App::uses('FileExplorer','Media.FileExplorer');

class FileExplorerController extends MediaAppController {

	public $uses = array();
	
	/**
	 * @var FileExplorer
	 */
	public $FileExplorer;
	
	protected $_dir = '/';
	protected $_file;
	protected $_config;
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		$this->_dir = $this->request->query('dir');
		$this->_file = $this->request->query('file');
		$this->_config = isset($this->passedArgs['config']) ? $this->passedArgs['config'] : null;
		
	}
	
	public function beforeRender() {
		$this->helpers['Media.FileExplorer'] = array(
			'config' => $this->_config,
			'dir' => $this->_dir,
			'file' => $this->_file
		);
	}
	
	/**
	 * Get FileExplorer instance
	 * 
	 * @param string $onfig FileExplorer config name
	 * @throws CakeException
	 * @return FileExplorer
	 */
	protected function &getFileExplorer($config = null) {
		
		if (!$this->FileExplorer) {
			
			if ($config === null)
				$config = $this->_config;
			
			$this->FileExplorer = new FileExplorer($config);
			$this->FileExplorer->autoLoadContents = true;
			$this->FileExplorer->dir($this->_dir);
		}
		return $this->FileExplorer;
	}
	
	/**
	 * Get FileExplorerFile instance of selected file
	 * 
	 * @deprecated
	 * @return boolean|FileExplorerFile
	 */
	protected function &getFile() {

		if (!$this->_file)
			return false;
		
		$File = $this->getFileExplorer()->file($this->_file);
		return $File;
	}
	
	/**
	 * Filebrowser index
	 * @param unknown_type $config
	 */
	public function admin_index($config = null) {
		
		if ($config) {
			$this->_config = $config;
		}
		
	}
	
	/**
	 * Open directory
	 */
	public function admin_open() {
		
		$fe = $this->getFileExplorer()->toArray();
		$this->set('fe',$fe);
	}
	
	/**
	 * Create a new folder
	 */
	public function admin_create() {

		if ($this->request->is('post')) {
				
			$data =& $this->request->data;
			try {
				$this->getFileExplorer($data['config'])
					->dir($data['dir'])
					->create($data['name'])
					->readContents();
				
				$this->Session->setFlash(__("Created dir %s",$data['name']),'success');
				$this->_dir = $data['dir'];
				$this->_config = $data['config'];
				$this->setAction('admin_open');
			} catch(Exception $e) {
				$this->Session->setFlash($e->getMessage(),'error');
			}
		} else {
			$this->request->data = array(
				'config'=>$this->_config,
				'dir' => $this->_dir
			);
		}
	}
	
	public function admin_move() {
		
		if ($this->request->is('post')) {

			$data =& $this->request->data;
			try {
				$this->getFileExplorer($data['config'])
				->dir($data['dir'])
				->move(null, $data['dir_new'])
				->readContents();
			
				$this->Session->setFlash(__("Moved dir %s to %s",$data['dir'],$data['dir_new']),'success');
				$this->_dir = $data['dir'];
				$this->_config = $data['config'];
				$this->setAction('admin_open');
			} catch(Exception $e) {
				$this->Session->setFlash($e->getMessage(),'error');
			}
		} else {
			$this->request->data = array(
					'config'=>$this->_config,
					'dir' => $this->_dir
			);
		}
	}
	
	/**
	 * Create a new folder
	 */
	public function admin_add() {
	
		if ($this->request->is('post')) {
	
			$data =& $this->request->data;
			try {
				$this->getFileExplorer($data['config'])
				->dir($data['dir'])
				->add($data['name'])
				->readContents();
	
				$this->Session->setFlash(__("Created file %s",$data['name']),'success');
				$this->_dir = $data['dir'];
				$this->_config = $data['config'];
				$this->setAction('admin_open');
			} catch(Exception $e) {
				$this->Session->setFlash($e->getMessage(),'error');
			}
		} else {
			$this->request->data = array(
					'config'=>$this->_config,
					'dir' => $this->_dir
			);
		}
	}	
	
	/**
	 * View file
	 */
	public function admin_view() {
		$file = $this->getFile();
		$this->set('file',$file->toArray());
	}
	
	/**
	 * Rename file
	 */
	public function admin_rename() {
		
		if ($this->request->is('post')) {
			
			$data =& $this->request->data;
			$this->getFileExplorer($data['config'])->dir($data['dir']);
			if ($this->FileExplorer->rename($data['file'],$data['file_new'])) {
				$this->Session->setFlash(__("Renamed %s to %s",$data['file'],$data['file_new']),'success');
			} else {
				$this->Session->setFlash(__("Renaming failed"),'error');
			}
						
		} else {
			$this->request->data = array(
				'name'=>basename($this->_file),
				'dir'=>$this->_dir,
				'file'=>$this->_file,
				'config'=>$this->_config
			);
		}
	}
	
	public function admin_delete() {
		
		if ($this->request->is('get')) {
			try {
				$this->getFileExplorer()->dir($this->_dir)->delete($this->_file);
				$this->Session->setFlash(__('Deleted file %s',$this->_file),'success');
			} catch(Exception $e) {
				$this->Session->setFlash($e->getMessage(),'error');
			}
		} else {
			$this->Session->setFlash(__('Invalid operation'),'error');
		}
		$this->redirect($this->referer());
	}
}