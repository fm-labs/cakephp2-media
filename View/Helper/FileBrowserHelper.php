<?php
App::uses('AppHelper','View/Helper');
App::uses('File','Utility');

/**
 * FileBrowser Helper
 * 
 * @author Flow
 * @property HtmlHelper $Html
 * @property PhpThumbHelper $PhpThumb
 */
class FileBrowserHelper extends AppHelper {
	
	public $helpers = array('Html', 'Media.PhpThumb');
	
	public $fileBrowser;
	
	protected $_imageExtensions = array('jpg','jpeg','gif','png','tiff','bmp');
	
	public function setFileBrowser(&$fileBrowser) {
		$this->fileBrowser =& $fileBrowser;
	}

/**
 * Checks if file is an image
 * 
 * @param string $file Filename
 * @return boolean
 */	
	public function isImage($file) {
		$ext = $this->getFileExt($file);
		return in_array($ext, $this->_imageExtensions);
	}

/**
 * Returns Html for the preview Image
 * 
 * @param string $file
 * @param array $options
 */	
	public function previewImage($file, $options = array()) {
		
		if (!$this->isImage($file)) {
			return $this->Html->image('/media/img/filebrowser/default-file-thumb.png',$options);
		}
		
		$options = array_merge(array(
			'width' => 50,
			'height' => 50,
			'url' => FULL_BASE_URL.$this->fileBrowser['baseUrl'].$this->fileBrowser['dir'].$file,
			'alt' => $file,
			'thumb' => array(
				'w' => 50,
				'h' => 50,
				'q' => 50,
			)
		), $options);	
		
		$url = $options['url'];
		unset($options['url']);
		
		
		$_filePath = $this->fileBrowser['cwd'] . $file;
		$_thumb = $this->PhpThumb->image($_filePath, $options);
		return $this->Html->link($_thumb, $url, array(
			'escape' => false,
			'target' => '_blank',
			'rel' => 'filebrowser',
			'title' => $file,
		));
	}
	
/**
 * Returns file extension of given filename
 * 
 * @param string $file
 * @return string
 */	
	public function getFileExt($file) {
		
		$file = basename($file);
		
		if (strrpos($file,'.') > 0) {
			return substr($file,strrpos($file,'.')+1);
		}
		
		return '';
	}

/**
 * Return file name without extension
 * 
 * @param string $file
 * @return string
 */	
	public function getFileName($file) {
		return basename($file, $this->getFileExt($file));
	}
	
}
?>
