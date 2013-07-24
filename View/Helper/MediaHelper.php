<?php
class MediaHelper extends AppHelper {
	
	public $helpers = array('Html');
	
	public function image($path, $options = array()) {

		try {
			list($id,$path,$url) = $this->getCachedPath($path, $options);
			return $this->Html->image($url, $options);
		} catch (Exception $e) {
			debug($e->getMessage());
		}
		
		return false;
	}
	
	public function imageUrl($path, $options = array(), $full = true) {
		
		try {
			list($id,$path,$url) = $this->getCachedPath($path, $options);
			return substr(Router::url('/',$full),0,-1).$url;
		} catch (Exception $e) {
			debug($e->getMessage());
		}
		
		return false;		
		
	}
	
	protected function getCachedPath($path, $options) {
		
		// path extension
		$basename = basename($path);
		if (strrpos($basename,'.') !== false) {
			$parts = explode('.', $basename);
			$ext = array_pop($parts);
			$dotExt = '.'.$ext;
			$filename = join('.',$parts);
		} else {
			return false;
		}
		
		$cacheId = md5($path.serialize($options));
		$cacheFile = Inflector::slug($filename) . '_' . $cacheId . $dotExt;
		$cachePath = MEDIA_THUMB_DIR . $cacheFile;
		$cacheUrl = MEDIA_THUMB_URL . $cacheFile;
		
		if (!file_exists($cachePath)) {
			copy($path, $cachePath);
		}
		
		return array($cacheId, $cachePath, $cacheUrl);
	}
	
}