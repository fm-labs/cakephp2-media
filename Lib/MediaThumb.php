<?php
App::import('Vendor', 'Media.phpthumb', true, array(), 'phpThumb' . DS . 'phpthumb.class.php');
App::uses('Inflector', 'Utility');

/**
 * Class MediaThumb
 *
 * Thumbnail generation for CakePHP using phpThumb
 */
class MediaThumb {

	protected $_source;

	protected $_params = array();

	protected $_path;

	protected $_disableCache = false;

	protected $_baseDir;

	protected $_baseUrl;

	protected $_tmpDir;

	public function __construct($source = null, $params = array()) {
		// set paths
		$this->_baseDir = (Configure::read('Media.Thumb.baseDir'))
			? Configure::read('Media.Thumb.baseDir')
			: WWW_ROOT . 'thumbs' . DS;

		$this->_baseUrl = (Configure::read('Media.Thumb.baseUrl'))
			? Configure::read('Media.Thumb.baseUrl')
			: '/thumbs/';

		$this->_tmpDir = (Configure::read('Media.Thumb.tmpDir'))
			? Configure::read('Media.Thumb.tmpDir')
			: TMP;

		$this->_disableCache = (Configure::read('Media.Thumb.disableCache'))
			? (bool)Configure::read('Media.Thumb.disableCache')
			: false;

		// setup default params
		$this->_params = array(
			//custom params
			'watermark'							=> null,
			'useImageMagick'					=> false,
			'imageMagickPath'					=> '/usr/bin/convert',

			//phpthumb params
			//'config_temp_directory' 			=> MEDIA_CACHE_DIR, //config_temp_directory
			//'config_cache_directory'			=> MEDIA_CACHE_DIR, //config_cache_directory
			'config_output_format'				=> 'jpeg', //config_output_format
			//'config_imagemagick_path'			=> null,
			//'config_prefer_imagemagick'		=> false,
			'config_error_die_on_error'			=> false,
			'config_document_root'				=> ROOT,
			'config_allow_src_above_docroot'	=> true, //IMPORTANT! //@todo make this optional
			#'config_cache_disable_warning'		=> !Configure::read('debug'),
			'config_cache_disable_warning'		=> true,
			#'config_disable_debug'				=> !Configure::read('debug'),
			'config_disable_debug'				=> true,
			'config_cache_prefix'				=> 'phpthumb_',
			//'config_cache_maxage'               => null,
			//'config_cache_maxsize'              => null,
			//'config_cache_maxfiles'             => null,
			'sia'								=> "thumbnail"
		);
		$this->set($params);

		// set source
		if ($source) {
			$this->setSource($source);
		}
	}

/**
 * Param setter
 *
 * @param      $key
 * @param null $val
 * @return $this
 */
	public function set($key, $val = null) {
		if (is_array($key)) {
			foreach ($key as $_k => $_v) {
				$this->set($_k, $_v);
			}
			return $this;
		}

		$this->_params[$key] = $val;
		return $this;
	}

/**
 * Source path setter
 *
 * @param $source
 * @return $this
 * @throws Exception
 */
	public function setSource($source) {
		if (!$source) {
			throw new Exception(__('MediaThumb: Invalid source'));
		}

		// os-specific directory separator
		$src = preg_replace('/\//', DS, $source);

		// relative paths
		if ($src[0] != "/" && !preg_match('/^[A-Z]\:\\\/', $src)) {
			$src = IMAGES . $src;
		}

		// verify
		if (!is_file($src) || !is_readable($src)) {
			throw new Exception(__('MediaThumb source file %s not accessible', $source));
		}

		$this->_source = $src;
		return $this;
	}

/**
 * Get path of rendered thumbnail
 *
 * @return mixed
 */
	public function getPath() {
		return $this->_path;
	}

/**
 * Get path of rendered thumbnail
 *
 * @return mixed
 */
	public function getUrl() {
		return $this->_baseUrl . basename($this->_path);
	}

/**
 * Build target filename from source and params
 *
 * @return string
 */
	protected function _getTargetFilename() {
		$src = $this->_source;
		$filename = Inflector::slug(basename($src));
		$hash = md5(serialize(array($src, $this->_params)));
		$ext = $this->_params['config_output_format'];

		return $filename . "_" . $hash . '.' . $ext;
	}

/**
 * Render thumbnail to file
 *
 * @throws Exception
 */
	public function renderToFile() {
		// check source
		$source = $this->_source;
		if (!$source) {
			throw new Exception(__('MediaThumb: No thumb source selected'));
		}

		// check thumb dir
		$dir = $this->_baseDir;
		if (!$dir) {
			throw new Exception('Media thumb dir is not configured');
		} elseif (!is_dir($dir) || !is_writable($dir)) {
			throw new Exception(__('Media thumb dir %s does not exist or is not writable', $dir));
		}

		// check target
		$target = $dir . $this->_getTargetFilename();

		// create thumb
		if (!file_exists($target) || $this->_disableCache === true) {
			//TODO dependency injection
			$engine = new phpthumb();
			$engine->setSourceFilename($source);
			foreach ($this->_params as $key => $val) {
				$engine->setParameter($key, $val);
			}

			if ($engine->GenerateThumbnail()) {

				if (!$engine->RenderToFile($target)) {
					throw new Exception('Rendering thumb to file failed');
				}
				//TODO re-evaluate; check if this is necessary
				@chmod($target, 0644);

				// debug('thumbnail created');
			}
			//debug($phpThumb->phpThumbDebug());
		}
		$this->_path = $target;
	}

/**
 * Wrapper for renderToFile()
 *
 * @return mixed
 */
	public function toFile() {
		$this->renderToFile();
		return $this->getPath();
	}

/**
 * @return void
 */
	public function renderToBrowser() {
		//TODO renderToBrowser() is not implemented yet
	}

/**
 * Static wrapper to create thumbs
 *
 * @param       $source
 * @param array $params
 * @return mixed
 */
	public static function create($source, $params = array()) {
		$_this = new self($source, $params);
		$_this->renderToFile();
		return $_this->getPath();
	}

/**
 * Static wrapper to get thumb url
 *
 * @param       $source
 * @param array $params
 * @return mixed
 */
	public static function url($source, $params = array()) {
		$_this = new self($source, $params);
		$_this->renderToFile();
		return $_this->getUrl();
	}

/**
 * Legacy wrapper
 *
 * @return mixed
 * @deprecated Use getPath() instead. We be removed in 2.1
 */
	public function getThumbPath() {
		return $this->getPath();
	}

/**
 * Legacy wrapper
 *
 * @return string
 * @deprecated Use getUrl() instead. Will be removed in 2.1
 */
	public function getThumbUrl() {
		return $this->getUrl();
	}

/**
 * Legacy wrapper for MediaThumb::create()
 *
 * @param       $source
 * @param array $params
 * @return mixed
 * @deprecated Use MediaThumb::create() instead. Will be removed in 2.1
 */
	public static function createThumb($source, $params = array()) {
		return self::create($source, $params);
	}

/**
 * Legacy wrapper for MediaThumb::url()
 *
 * @param       $source
 * @param array $params
 * @return mixed
 * @deprecated Use MediaThumb::url() instead. Will be removed in 2.1
 */
	public static function createThumbUrl($source, $params = array()) {
		return self::url($source, $params);
	}

}