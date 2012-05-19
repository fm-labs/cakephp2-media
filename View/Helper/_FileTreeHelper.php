<?php
class FileTreeHelper extends AppHelper {
	
	public $helpers = array('Html');
	
	public $fileTreeJs = '/media/js/jquery.file_tree/jqueryFileTree';
	public $fileTreeCss = '/media/css/jquery.file_tree/jqueryFileTree';
	
	public function includeFileTree() {
		$this->Html->css($this->fileTreeCss,null,array('inline'=>false));
		$this->Html->script($this->fileTreeJs,array('inline'=>false));
	}
	
	public function fileTree($dir = '/', $params = array()) {

		$this->includeFileTree();
		
		$params = array_merge(array(
			'root' => $dir,
			'script' => $this->url(array('controller'=>'file_browser','action'=>'filetree','plugin'=>'media')),
			'expandSpeed' => 1000,
			'collapseSpeed' => 1000,
			#'expandEasing' => null,
			#'collapseEasing' => null,
			'multiFolder' => false,
			'folderEvent' => 'click',
			'callback' => "function(file) { console.log(file); });"
		),$params);
		
	}

/**
 * Formats a Folder::read() result to jqueryFileTree output
 * Example Output:
 * 	<ul class="jqueryFileTree" style="display: none;">
    	<li class="directory collapsed"><a href="#" rel="/this/folder/">Folder Name</a></li>
    	<li class="file ext_txt"><a href="#" rel="/this/folder/filename.txt">filename.txt</a></li>
	</ul>
 * @param mixed $files
 */	
	public function formatOutput($files = array(), $root = '') {
		
		$out = "";
		//directories
		if (isset($files[0])):
			foreach($files[0] as $dir) {
				if (in_array($dir,array('.svn')))
					continue; 
					
				$out .= $this->Html->tag('li',
					$this->Html->link($dir, '#', array('rel'=>$root.$dir.'/')),
					array('class' => 'directory collapsed')
				);
			}
		endif;
		
		//files
		/*
		if (isset($files[1])):
			foreach($files[1] as $file) {
				$out .= $this->Html->tag('li',
					$this->Html->link($file, '#', array('rel'=>base64_encode($root.$file))),
					array('class' => 'file ext_txt')
				);
			}
		endif;
		*/
		
		$out = $this->Html->tag('ul',$out,array('class'=>'jqueryFileTree'));
		return $out;
	}
	
}
?>