<?php
App::import('Vendor', 'Media.phpthumb', true, array(), 'phpThumb' . DS . 'phpthumb.class.php');
App::uses('Inflector', 'Utility');

class MediaThumb {

	protected $_source;

	protected $_params = array();

	protected $_path;

	protected $_disableCache = false;

	protected $_baseDir;

	protected $_baseUrl;

	protected $_tmpDir;

	public function __construct($source, $params = array()) {
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

		$this->setSource($source);
		$this->set($params);

		//$this->_disableCache = Configure::read('Media.thumbNoCache');
	}

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

	public function setSource($filePath) {
		// os-specific directory separator
		$src = preg_replace('/\//', DS, $filePath);

		// relative paths
		if ($src[0] != "/" && !preg_match('/^[A-Z]\:\\\/', $src)) {
			$src = IMAGES . $src;
		}

		$this->_source = $src;
		return $this;
	}

	protected function _getTargetFilename() {
		$src = $this->_source;
		$filename = Inflector::slug(basename($src));
		$hash = md5(serialize(array($src, $this->_params)));
		$ext = $this->_params['config_output_format'];
		$dir = $this->_baseDir;

		if (!$dir) {
			throw new CakeException('Media thumb dir is not configured');
		}

		if (!is_dir($dir) || !is_writable($dir)) {
			throw new CakeException(__('Media thumb dir %s does not exist or is not writable', $dir));
		}

		return $dir . $filename . "_" . $hash . '.' . $ext;
	}

	public function renderToFile() {
		$source = $this->_source;
		$target = $this->_getTargetFilename();

		//TODO dependency injection
		$engine = new phpthumb();
		$engine->setSourceFilename($source);
		foreach ($this->_params as $key => $val) {
			$engine->setParameter($key, $val);
		}

		if (!file_exists($target) || $this->_disableCache === true) {
			if ($engine->GenerateThumbnail()) {

				if (!$engine->RenderToFile($target)) {
					throw new CakeException('Rendering thumb to file failed');
				}
				//TODO re-evaluate; check if this is necessary
				@chmod($target, 0644);

				// debug('thumbnail created');
			}
			//debug($phpThumb->phpThumbDebug());
		}
		$this->_path = $target;
	}

	public function renderToBrowser() {
		//TODO renderToBrowser() is not implemented yet
	}

	public function getThumbPath() {
		return $this->_path;
	}

	public function getThumbUrl() {
		return $this->_baseUrl . basename($this->_path);
	}

	public static function createThumb($source, $params = array()) {
		$_this = new self($source, $params);
		$_this->renderToFile();
		return $_this->getThumbPath();
	}

	public static function createThumbUrl($source, $params = array()) {
		$_this = new self($source, $params);
		$_this->renderToFile();
		return $_this->getThumbUrl();
	}

}