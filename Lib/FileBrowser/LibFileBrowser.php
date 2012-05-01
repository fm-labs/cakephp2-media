<?php
App::uses('LibFileBrowserBase', 'Media.FileBrowser');
App::uses('LibFileBrowserDir', 'Media.FileBrowser');
App::uses('LibFileBrowserFile', 'Media.FileBrowser');

/**
 * LibFileBrowser
 * @author Flow
 *
 */
class LibFileBrowser {

	public $cmd = "index";
	
/**
 * LibFileBrowserBase instance
 * @var LibFileBrowserBase
 */	
	public $Base;
	
/**
 * LibFileBrowserDir instance
 * @var LibFileBrowserDir
 */	
	public $Dir;
	
/**
 * LibFileBrowserFile instance
 * @var LibFileBrowserFile
 */	
	public $Resource;
	
	public function __construct($basePath = TMP, $dirPath = '/', $filePath = null) {
		
		$this->Base = new LibFileBrowserBase($basePath);
		$this->Dir = new LibFileBrowserDir($this->Base,$dirPath);
		$this->Resource = new LibFileBrowserFile($this->Dir, $filePath);
		
	}
}