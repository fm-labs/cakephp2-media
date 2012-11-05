<?php

if (!defined('MEDIA_THUMB_TMP_DIR')) define('MEDIA_THUMB_TMP_DIR',TMP."phpthumb".DS); 	
if (!defined('MEDIA_THUMB_CACHE_DIR')) define('MEDIA_THUMB_CACHE_DIR',MEDIA_THUMB_TMP_DIR."cache".DS); 	

if (!defined('MEDIA_THUMB_DIR')) define('MEDIA_THUMB_DIR',WWW_ROOT."cache/");

App::import('Vendor','Media.phpthumb', true , array(), 'phpThumb'.DS.'phpthumb.class.php');

class LibPhpThumb {

/**
 * Get Thumbnail path for given source and params
 * If thumbnail does not exist it will be rendered to file
 * 
 * @param string $source Absolute path to source file
 * @param string $target Basename of thumbnail. Use NULL (default) is recommendend.
 * @param array $params PhpThumb params
 * @return array Array list with format: array($path, $url)
 */	
	static public function getThumbnail($source, $target = null, $params = array(), $force = false) {
		
		if (!$force && file_exists(self::target($source, $target = null, $params)))
			return $target;
		
		$path = self::createThumbnail($source, $target, $params);
		$url = self::getThumbnailUrl($path);
		
		return array($path, $url);
	}

/**
 * Get Thumbnail URL for given source and params
 * 
 * @param string $source Absolute path to source file
 * @param array $params PhpThumb params
 * @param boolean $full Full Url
 */	
	static public function getThumbnailUrl($path, $full = false) {
		
		if (!$path)
			return false;

		$pattern = '/^'.preg_quote(WWW_ROOT, '/').'(.*)$/i';
		if (!preg_match($pattern, $path, $matches))
			return false;
		
		$url = '/'.$matches[1];
		if ($full)
			$url = Router::url('/',$full) . $matches[1];
		
		return $url;
	}

/**
 * Renders the Thumbnail for given source
 * 
 * 
 * @param unknown_type $source
 * @param unknown_type $target
 * @param unknown_type $params
 * @throws NotFoundException
 * @throws CakeException
 */	
	static public function createThumbnail($source, $target = null, $params = array()) {
		
		$params = array_merge(array(
			//custom params
			'watermark'							=> null,
			'useImageMagick'					=> false,
			'imageMagickPath'					=> '/usr/bin/convert',
			//phpthumb params
			'config_temp_directory' 			=> MEDIA_THUMB_TMP_DIR, //config_temp_directory
			'config_cache_directory'			=> MEDIA_THUMB_CACHE_DIR, //config_cache_directory
	        'config_output_format'				=> 'jpg', //config_output_format
			//'config_imagemagick_path'			=> null,
			//'config_prefer_imagemagick'		=> false,
			'config_error_die_on_error'			=> false,
			'config_document_root'				=> ROOT,
			'config_allow_src_above_docroot'	=> true, //IMPORTANT!
			#'config_cache_disable_warning'		=> !Configure::read('debug'),
			'config_cache_disable_warning'		=> true,
			#'config_disable_debug'				=> !Configure::read('debug'),
			'config_disable_debug'				=> true,
			'config_cache_prefix'				=> 'cache_'	,
			//'config_cache_maxage'               => null,
			//'config_cache_maxsize'              => null,
			//'config_cache_maxfiles'             => null,
			'sia'								=> "thumbnail"		
		),$params);		
		
		
		if (!file_exists($source))
			throw new NotFoundException(__d('media',"File %s not found",$source));
		
		// Configuring thumbnail settings
		$phpThumb = new phpthumb;
		self::_applyParams($phpThumb, $params);
		
		$phpThumb->setSourceFilename($source);

		$target = self::target($source, $target, $params);
		
		// Creating thumbnail
		if ($phpThumb->GenerateThumbnail()) {
			if (!$phpThumb->RenderToFile($target)) {
				throw new CakeException('Could not render image to: ' . $target);
			}
			@chmod($target, 0644); //TODO check if this is necessary
		}
		//debug($phpThumb->phpThumbDebug());
		
		return $target;
	}

	/**
	 * Get Target filepath for given source and params
	 * 
	 * @param string $source
	 * @param string $target	Name of thumb. Pass NULL (default) is recommendend
	 * @param mixed $params		Use params to create a unique thumb for each config
	 */	
	static public function target($source, $target = null, $params = array()) {
		
		if (!$target) {
			list($filename, $ext, $dotExt) = self::splitBasename(basename($source));
			$target = $filename."_".md5(serialize(array($source,$params))).$dotExt;
		}
		
		return MEDIA_THUMB_DIR . $target;
	}

	static public function splitBasename($basename) {
	
		if (strrpos($basename,'.') !== false) {
			$parts = explode('.', $basename);
			$ext = array_pop($parts);
			$dotExt = '.'.$ext;
			$filename = join('.',$parts);
		} else {
			$ext = $dotExt = null;
			$filename = $basename;
		}
	
		return array($filename, $ext, $dotExt);
	
	}
/**
 * Apply Params to phpthumb instance
 * 
 * @param phpthumb $phpThumb
 * @param mixed $params
 */	
	static protected function _applyParams(phpthumb &$phpThumb, $params = array()) {

		//watermark
		if ($params['watermark']){
			$params['fltr'] = array("wmi|". IMAGES . $params['watermark']."|BR|50|5");
		}
		unset($params['watermark']);
		
		/*
		$imageArray = explode(".", $source);
		$phpThumb->config_output_format = $imageArray[1];
		unset($imageArray);
		*/

		$params['config_prefer_imagemagick'] = $params['useImageMagick'];
		$params['config_imagemagick_path'] = $params['imageMagickPath'];		
		
		$classVars = get_class_vars(get_class($phpThumb));
		foreach($params as $k => $v) {
			if (!array_key_exists($k, $classVars)) continue;
			
			$phpThumb->setParameter($k, $v);
		}
	}
	
}
?>