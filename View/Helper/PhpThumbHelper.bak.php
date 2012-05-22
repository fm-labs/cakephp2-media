<?php
#ini_set('memory_limit', '128M');
App::import('Vendor','Media.phpthumb', true , array(), 'phpThumb-1.7.11'.DS.'phpthumb.class.php');

class PhpThumbHelper extends AppHelper {

	public $helpers = array('Html');
	
/**
 * Instance of phpThumb
 * 
 * @var phpThumb object
 */	
	private $phpThumb			= null;

	
	private $_source = null;
	private $_target = null;
	
/**
 * phpThumb settings
 * 
 * @var mixed
 */	
	private $settings = array(
		'config_temp_directory' 			=> PHPTHUMB_TEMP_DIR, //config_temp_directory
		'config_cache_directory'			=> PHPTHUMB_CACHE_DIR, //config_cache_directory
        'w'									=> 150, //w
        'h'									=> 225, //h
        'q'									=> 75, //q
        'config_output_format'				=> 'jpg', //config_output_format
		'config_imagemagick_path'			=> null,
		'config_prefer_imagemagick'			=> false,
		'config_error_die_on_error'			=> false,
		'config_document_root'				=> '',
		'config_allow_src_above_docroot'	=> true, //IMPORTANT!
		'config_cache_disable_warning'		=> false,
		'config_disable_debug'				=> true,
		'config_cache_prefix'				=> 'cache_'	,
		'config_cache_maxage'               => null,
		'config_cache_maxsize'              => null,
		'config_cache_maxfiles'             => null,		
	);
	

/**
 * Returns current instance of phpThumb. 
 * Creates new phpThumb instance if instance is empty
 * 
 * @access public
 * @param $new If true, the creation of a new instance will be forced
 * @return phpThumb object
 */	
	public function __construct(&$View, $settings = array()) {
		parent::__construct($View,$settings);

		$this->createPhpThumb();
	}	

	public function createPhpThumb() {
		//create phpThumb instance
		$this->phpThumb = new phpthumb();
	}
	
	
	public function create($sourcePath,$settings = array()) {
		$this->createPhpThumb();
		$this->setSource($sourcePath);
		$this->setSettings($settings);
		
	}

/**
 * Set filepath of the source image
 * 
 * @access public
 * @param $imagepath
 * @return boolean Returns true if $imagepath exists
 */	
	public function setSource($imagepath = null) {
		$this->_source = null;
		if (!$imagepath) {
			$this->log("Set SourceImage failed because imagepath is empty");
			return false;
		}		
		if (!file_exists($imagepath)) {
			$this->log("Set SourceImage failed because $imagepath does not exist");
			return false;
		}
		$path_info = pathinfo($imagepath);
	    if (!in_array($path_info['extension'], array('jpg','jpeg','png','tif','gif','bmp'))) {
        	$this->log($path_info['extension']. " is not a valid image extension");
        	return false;
        }		
		
		$this->_source = $imagepath;
		return $this;
	}

/**
 * Set Settings
 * 
 * @access public
 * @param $settings
 */	
	public function setSettings($settings = array(),$reset = false) {
		if (!$settings)
			return null;
			
		if ($reset)
			$this->settings = $settings;
		else
			$this->settings = Set::merge($this->settings, $settings);
	}
	
/**
 * Wrapper function to set phpThumb parameters
 * 
 * @access public
 * @param $name
 * @param $value
 */	
	public function setParameter($name, $value = null,$safe = true) {
		//get phpThumb instance
        $phpThumbVars = get_object_vars($this->phpThumb);
        
        //set if object var
        if (array_key_exists($name, $phpThumbVars)) {
        	if ($safe)
        		$this->settings[$name] = $value;
        	else
        		$this->phpThumb->setParameter($name, $value);
			#$this->log("setParameter $name to $value");
        } else {
			$this->log("setParameter failed because property $name does not exist in Class 'phpthumb'");        	
        }
        
	}
	
	public function path($source = null, $settings = array()) {
		$this->create($source,$settings);
		return $this->render(false);
	}
	
	public function url($source = null, $settings = array()) {
		$full = (isset($settings['full_url'])) ? $settings['full_url'] : false;
		unset($settings['full_url']);
		
		#debug($source);
		$thumbPath = $this->path($source, $settings);
		#debug($thumbPath);
		if (!$thumbPath)
			return false;
			
		$thumbFile = basename($thumbPath);
		$wwwPath = substr(Router::url('/',$full),0,-1) . PHPTHUMB_CACHE_WWW . $thumbFile;
		return $wwwPath;
	}
	
	public function image($source = null, $settings = array(), $imageOptions = array()) {
		if (isset($imageOptions['width']) && !isset($settings['w'])) {
			$settings['w'] = $imageOptions['width'];
			unset($imageOptions['width']);
		}
		if (isset($imageOptions['height']) && !isset($settings['h'])) {
			$settings['h'] = $imageOptions['height'];
			unset($imageOptions['height']);
		}
		$url = $this->url($source,$settings);
		if (!$url)
			return false;

		#debug($url);	
			
		return $this->Html->image($url, $imageOptions);
	}

/**
 * Renders the thumbnail of the $imagepath sourceimage
 * 
 * @access public
 * @param $imagepath if not set in create() the $imagepath can be defined here
 * @param $settings additional settings
 * @param $reset We need to create a new instance if we want to render multiple images
 * @return string If RenderImagetoFile: returns filepath of the rendered image. If RenderImagetoBrowser outputs image with headers 
 */	
    public function render($renderToBrowser = false){
        
    	if (empty($this->_source) || !file_exists($this->_source)) {
    		$this->log("Could not render PhpThumb for '%s'.File not found.",$this->_source);
    		return false;
    	}
    	
    	$reset = false; 
    	
        //get instance
		$this->createPhpThumb();
        $phpThumb =& $this->phpThumb;
        
        //parse settings
        $this->_parseSettings();
        
        //render
        $phpThumb->setSourceFilename($this->_source);
        if ($renderToBrowser):
    		if($phpThumb->GenerateThumbnail()) {
		        if($phpThumb->RenderOutput()) {
                	$phpThumb->OutputThumbnail();
		        } else {
					$this->log('RenderOuput() Failed:<pre>'.$phpThumb->fatalerror."\n\n".implode("\n\n", $phpThumb->debugmessages).'</pre>');	        	
		        }
            } else {
				$this->log('GenerateThumbnail() Failed:<pre>'.$phpThumb->fatalerror."\n\n".implode("\n\n", $phpThumb->debugmessages).'</pre>');
            }
        else:
        	#$path_info = pathinfo($this->_target);
        	#$cacheFilename = $path_info['basename'];
	        #$this->setParameter('cache_filename', $phpThumb->config_cache_directory.$cacheFilename);
	        //check cache
	        if(!is_file($phpThumb->cache_filename)){
	        	#$this->log('Thumbnail does not exist -> GenerateThumbnail()');
	            if($phpThumb->GenerateThumbnail()) {
	                if (!$phpThumb->RenderToFile($phpThumb->cache_filename)) {
						$this->log('RenderToFile() Failed:<pre>'.$phpThumb->fatalerror."\n\n".implode("\n\n", $phpThumb->debugmessages).'</pre>');
	                }
	            } else {
					// do something with debug/error messages
					$this->log('GenerateThumbnail() Failed:<pre>'.$phpThumb->fatalerror."\n\n".implode("\n\n", $phpThumb->debugmessages).'</pre>');
	            }
	        }
	        
	        if(is_file($phpThumb->cache_filename)){
	        	#$this->log("Found cached file ".$phpThumb->cache_filename);
	            return $phpThumb->cache_filename;
	        } else {
	        	$this->log(__("Rendering failed: %s not found",$phpThumb->cache_filename));
	        	return false;
	        }
        endif;      
    }
    
    function _parseSettings() {
    	foreach ($this->settings as $name => $val):
    		$this->setParameter($name, $val,false);
    	endforeach;
    }
    
    function log($msg) {
    	if (Configure::read('debug')>0) {
	    	#debug('PhpThumbHelper:'.$msg);
    	}
    	parent::log('PhpThumbHelper:'.$msg,'phpthumb');
    }
}
?>