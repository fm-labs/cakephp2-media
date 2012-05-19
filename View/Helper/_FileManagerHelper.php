<?php
App::uses('FileManagerComponent','Media.Controller/Component');

class FileManagerHelper extends AppHelper {
	
	private $_data;
	
	public $dirCommands;
	
	public $fileCommands;
	
	public function __construct(&$View,$settings=array()){
		parent::__construct(&$View,$settings);
	}
	
	public function bind($data) {
		$this->_data = $data;
	}
	
	public function bindCommands($mode,$cmds) {
		$_commands = array();
		foreach($cmds as $cmd => $params) {
			if (is_numeric($cmd)) {
				$cmd = $params;
				$params = array();
			}
			$_command = Set::merge(array(
				'title' => Inflector::humanize($cmd),
				'url' => array()
			),$params);
			
			$_commands[$cmd] = $_command;
		}
		$__var = $mode."Commands";
		$this->{$__var} = $_commands;
	}
	public function bindDirCommands($cmds){
		$this->bindCommands('dir',$cmds);
	}
	
	public function bindFileCommands($cmds) {
		$this->bindCommands('file',$cmds);
	}
	
	public function dirActionUrl($dir,$cmd='index') {
		$_dir = $dir;
		if ($dir[0] != '/')
			$dir = $this->_data['dir'].$dir;
		
		$url = array(
			'action' => 'dir_'.$cmd,
			//'mode' => 'dir',
			'dir' => FileManagerComponent::encodePath($dir),
			$_dir,
		);
		if (isset($this->dirCommands[$cmd])) {
			$url = array_merge($url,$this->dirCommands[$cmd]['url']);
		}
		
		return $url;
	}
	
	public function fileActionUrl($file,$cmd='view') {
		
		$url = array(
			'action' => 'file_'.$cmd,
			'mode' => 'file',
			'dir' => FileManagerComponent::encodePath($this->_data['dir']),
			'file' => FileManagerComponent::encodePath(basename($file)),
		);
		
		return $url;
		
	}
	
	public function actionUrl($action, $cmd, $path = '', $file = null, $url=array()) {
		if ($path[0] != '/')
			$path = $this->_data['dir'].$path;
			
		$url = array_merge(array(
			'mode' => $action,
			'action' => $action,
			'cmd' => $cmd,
			'path' => FileManagerComponent::encodePath($path),
			'file' => FileManagerComponent::encodePath($file),
		),$url);
		
		return $url;
	}
	
	private function _mapAction($action) {
		if (!isset($this->_data['actionMap']) || !isset($this->_data['actionMap'][$action]))
			return $action;
			
		return $this->_data['actionMap'][$action];
	}
}
?>