<?php
class FileBrowserHelper extends AppHelper {

	public $helpers = array('Html');
	
	private $settings =  array(
		'cssFile' => '/media/css/filebrowser'
	);
/**
 * @var LibFileBrowser
 */	
	public $Browser;
	
	public function __construct($View, $settings = array()) {
		parent::__construct($View, $settings);
		
		$this->bindCss();
	}
	
	public function bindCss($css = null) {
		if (!$css)
			$css = $this->settings['cssFile'];
			
		$this->Html->css($css,null,array('inline'=>false));
	}
	
	public function set(LibFileBrowser $fileBrowser) {
		$this->Browser =& $fileBrowser;
	}
	
	public function buildUrl($url = array()) {
		$url = array_merge(array(
			'dir' => true,
			'file' => true,
		),$url);
		
		$url['dir'] = ($url['dir'] && $this->Browser->Dir->path()) ? base64_encode($this->Browser->Dir->path()) : null;
			
		$url['file'] =($url['file'] && $this->Browser->Resource->path()) ? base64_encode($this->Browser->Resource->path()) : null;
			
		return $url;
	}
	
	public function url($url, $full = false) {
		return $this->Html->url($this->buildUrl($url),$full);
	}
	
	public function directory($options = array()) {
		$fileBrowser =& $this->Browser;
		
		$options = array_merge(array(
			'layout' => 'view'
		),$options);
		
		return $this->_View->element(
			'Media.FileBrowser/directory/'.$options['layout'],
			compact('options','fileBrowser')
		);
	}
	
	public function upload($options = array()) {
		
		$options = Set::merge(array(
			'uploaderUrl' => array('action'=>'upload'),
			'layout' => 'uploadify'
		),$options);
		
		return $this->_View->element(
			'Media.FileBrowser/upload/'.$options['layout'],
			compact('options')
		);
	}
}
?>