<?php
App::uses('MediaAppController','Media.Controller');
class MediaController extends MediaAppController {

	public function admin_index() {
		
		$config = Configure::read('Media');
		$flags = array('MEDIA_BOOTSTRAPED','MEDIA_ROUTED');
		$dirs = array(
				'MEDIA_DATA_DIR','MEDIA_ATTACHMENT_DIR',
				'MEDIA_UPLOAD_DIR','MEDIA_CACHE_DIR','MEDIA_THUMB_DIR'
		);
		$urlPaths = array('MEDIA_DATA_URL','MEDIA_THUMB_URL');
		
		$paths = array();
		foreach($dirs as $dir) {
			
			$defined = $path = $exists = $writeable = false;
			
			if (defined($dir)) {
				$defined = true;
				$path = constant($dir);
				if (is_dir($dir)) {
					$exists = true;
					if (is_writable($dir)) {
						$writeable = true;
					}
				}
			}
			
			$paths[$dir] = compact('defined','path','exists','writeable');
		}

		$this->set(compact('config','paths','urlPaths','flags'));
	}

}