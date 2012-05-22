<?php

if (!defined('MEDIA_PHPTHUMB_TEMP_DIR')) define('MEDIA_PHPTHUMB_TEMP_DIR',TMP."phpthumb".DS); 	
if (!defined('MEDIA_PHPTHUMB_CACHE_DIR')) define('MEDIA_PHPTHUMB_CACHE_DIR',CACHE."phpthumb".DS); 	
if (!defined('MEDIA_PHPTHUMB_TARGET_DIR')) define('MEDIA_PHPTHUMB_TARGET_DIR',IMAGES."thumbs".DS); 	
if (!defined('MEDIA_PHPTHUMB_WWW_DIR')) define('MEDIA_PHPTHUMB_WWW_DIR',"thumbs/"); 	

App::import('Vendor','Media.phpthumb', true , array(), 'phpThumb'.DS.'phpthumb.class.php');

class LibPhpThumb {

	static public function createThumbnail($source, $target = null, $params = array()) {
		
		$params = array_merge(array(
			//custom params
			'watermark'							=> null,
			'useImageMagick'					=> false,
			'imageMagickPath'					=> '/usr/bin/convert',
			//phpthumb params
			'config_temp_directory' 			=> MEDIA_PHPTHUMB_TEMP_DIR, //config_temp_directory
			'config_cache_directory'			=> MEDIA_PHPTHUMB_CACHE_DIR, //config_cache_directory
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
			throw new NotFoundException(__("File %s not found",$source));
		
		// Configuring thumbnail settings
		$phpThumb = new phpthumb;
		self::_applyParams($phpThumb, $params);
		
		$phpThumb->setSourceFilename($source);

		if (!$target)
			$target = self::target($source, $params);
		
		// Creating thumbnail
		if ($phpThumb->GenerateThumbnail()) {
			if (!$phpThumb->RenderToFile($target)) {
				throw new CakeException('Could not render image to: ' . $target);
				return false;
			}
		}
		#debug($phpThumb->phpThumbDebug());
		
		return true;
	}

/**
 * Get Target filepath for given source and params
 * 
 * @param string $source
 * @param mixed $params
 */	
	static public function target($source, $params = array()) {
		App::uses('File','Utility');
		
		$w = (isset($params['w'])) ? $params['w'] : null;
		$h = (isset($params['h'])) ? $params['h'] : null;
		$q = (isset($params['q'])) ? $params['q'] : null;
		
		
		$File = new File($source,false);
		
		return sprintf("%s%s_%s_%s_%s_%s.%s",
			MEDIA_PHPTHUMB_TARGET_DIR,$File->name(),$File->md5(),strval($w),strval($h),strval($q),$File->ext());
	}
	
	static public function getThumbnailUrl($source, $params = array()) {
		$thumbnail = self::getThumbnail($source, $params);
		if (!$thumbnail)
			return false;
		
		return MEDIA_PHPTHUMB_WWW_DIR . basename($thumbnail);
	}

/**
 * Get Thumbnail path for given source and params
 * If no thumbnail exists it will be rendered to file
 * 
 * @param string $source
 * @param mixed $params
 */	
	static public function getThumbnail($source, $params = array()) {
		$target = self::target($source,$params);
		if (!file_exists($target)) {
			try {
				self::createThumbnail($source,$target,$params);
			} catch(Exception $e) {
				CakeLog::write('phpthumb',$e->getMessage());
				return false;
			}
		}
			
		return $target;
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