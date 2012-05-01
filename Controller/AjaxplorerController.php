<?php
define('MEDIA_AJAXPLORER_VENDORPATH', App::pluginPath('Media').'Vendor'.DS.'ajaxplorer-core-4.0.4'.DS);
define('MEDIA_AJAXPLORER_APPPATH', ROOT.DS.'tools'.DS.'ajaxplorer'.DS);

class AjaxplorerController extends MediaAppController {
	
	public function login() {
		debug(MEDIA_AJAXPLORER_APPPATH);
		debug($_REQUEST);
		debug($_SERVER['QUERY_STRING']);
		debug($this->request->params);
		$this->layout = "empty";
	}
	
	public function admin_file($resource = null) {
		
		if(is_null($resource) && $this->request->params['resource'])
			$resource = $this->request->params['resource'];
		
		//$includeFile = MEDIA_AJAXPLORER_VENDORPATH . $resource . "?" . http_build_query($this->request->query);
		$includeFile = MEDIA_AJAXPLORER_VENDORPATH . $resource;
		//debug($includeFile);
		
		$this->set(compact('includeFile'));
		
	}
	
	public function admin_plugin() {
		$this->autoLayout = false;
		/*
		debug ($this->passedArgs);
		debug($resource);
		debug($_SERVER['QUERY_STRING']);
		debug($this->params);
		*/
		
		$filepath = MEDIA_AJAXPLORER_VENDORPATH.'plugins'.DS.join(DS,$this->passedArgs);
		//debug($filepath);
		
		App::uses('File','Utility');
		$File = new File($filepath,false);
		
		$this->response->disableCache();
		$this->response->type($File->ext());
		$this->response->body($File->read());
		$this->response->send();
	}
	
	public function beforeRender() {
		$this->set('ajaxplorerRootPath',MEDIA_AJAXPLORER_VENDORPATH);	
	}
	
}
?>