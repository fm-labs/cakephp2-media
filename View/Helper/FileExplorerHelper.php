<?php
App::uses('AppHelper','View/Helper');

/**
 * FileExplorer Helper
 * 
 * @property HtmlHelper $Html
 */
class FileExplorerHelper extends AppHelper {
	
	public $helpers = array('Html');
	
	public $settings = array(
		'config' => null	
	);
	
	public function config($configName = null) {
		if ($configName === null)
			return $this->settings['config'];
		
		$this->settings['config'] = $configName;
		return $this;
	}
	
	public function actionUrl($action, $dir = null, $file = null, $full = false) {
		
		$url = array(
			'plugin' => 'media',
			'controller' => 'file_explorer',
			'action' => $action,
			'config' => $this->settings['config']
		);
		$url = parent::url($url,$full);
		
		//$dir = urlencode($dir);
		//$file = urlencode($file);
		$url .= '?'.http_build_query(compact('dir','file'));
		
		return $url;
	}
	
}
?>
