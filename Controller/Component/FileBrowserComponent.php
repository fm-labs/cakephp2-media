<?php
App::uses('Component','Controller/Component');
App::uses('FileBrowser','Media.FileSystem');

/**
 * FileBrowserComponent
 * 
 * @author Flow
 * @property SessionComponent $Session
 */
class FileBrowserComponent extends Component {

	
	public $components = array('Session');
	
	public $FileBrowser;
	
	public function initialize($controller) {
		
		if ($this->Session->check('Media.FileBrowser')) {
			$this->FileBrowser = $this->Session->read('Media.FileBrowser');
		} else {
			$this->FileBrowser = new FileBrowser();
		}
	}
	
	public function open($dir) {
	}

/**
 * @see Component::beforeRender()
 */
	public function beforeRender(&$controller) {
		$controller->set('fileBrowser',$this->FileBrowser->toArray());
	}	
	

}
?>