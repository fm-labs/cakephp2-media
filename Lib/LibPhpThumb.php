<?php
#ini_set('memory_limit', '128M');
App::import('Vendor','FileManager.phpthumb', true , array(), 'phpThumb_1.7.11'.DS.'phpthumb.class.php');

class LibPhpThumb {

	public $cache_www = PHPTHUMB_CACHE_WWW;
	
	public $base_url = FULL_BASE_URL;
	
/**
 * Instance of phpThumb
 * 
 * @var phpThumb object
 */	
	private $phpThumb			= null;

	
	private $_sourceFilePath = null;
	private $_thumbFilePath = null;
	private $_thumbUrl = null;
	private $_thumbUrlFull = null;
	
/**
 * phpThumb settings
 * 
 * @var mixed
 */	
	private $defaults = array(
		'config_temp_directory' 			=> PHPTHUMB_TEMP_DIR, //config_temp_directory
		'config_cache_directory'			=> PHPTHUMB_CACHE_ROOT, //config_cache_directory
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
		'config_disable_debug'				=> false,
		'config_cache_prefix'				=> 'cache_'	,
		'config_cache_maxage'               => null,
		'config_cache_maxsize'              => null,
		'config_cache_maxfiles'             => null,
		'sia'								=> "thumbnail"		
	);
	
	private $settings = array();
/**
 * Returns current instance of phpThumb. 
 * Creates new phpThumb instance if instance is empty
 * 
 * @access public
 * @param $new If true, the creation of a new instance will be forced
 * @return phpThumb object
 */	
	public function __construct($sourceFilePath = null, $settings = array()) {
		$this->reset();
		
		if ($sourceFilePath)
			$this->setSource($sourceFilePath);
			
		$this->setSettings($settings);
		
	}	

	public function init() {
		//create phpThumb instance
		$this->phpThumb = new phpthumb();
	}
	
	public function reset() {
		$this->settings = $this->defaults;
		
		$this->_sourceFilePath = null;
		$this->_thumbFilePath = null;
		$this->_thumbUrl = null;
		$this->_thumbUrlFull = null;
		
		$this->init();
	}

/**
 * Set filepath of the source image
 * 
 * @access public
 * @param $imagepath
 * @return boolean Returns true if $imagepath exists
 */	
	public function setSource($sourceFilePath = null) {
		$this->_sourceFilePath = null;
		if (!$sourceFilePath) {
			$this->log("Set SourceImage failed because imagepath is empty");
			return false;
		}		
		if (!file_exists($sourceFilePath)) {
			$this->log("Set SourceImage failed because $sourceFilePath does not exist");
			return false;
		}
		$path_info = pathinfo($sourceFilePath);
	    if (!in_array($path_info['extension'], array('jpg','jpeg','png','tif','gif','bmp'))) {
        	$this->log($path_info['extension']. " is not a valid image extension");
        	return false;
        }		
		
		$this->_sourceFilePath = $sourceFilePath;
		
    	$this->setParameter('sia', basename($sourceFilePath));
		return true;
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
 * @param $safe
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
	
	public function getThumbFilePath() {
		if ($this->_thumbFilePath !== null)
			return $this->_thumbFilePath;
			
		$this->_thumbFilePath = $this->render(false);
		return $this->_thumbFilePath;
	}
	

/**
 * Returns thumb url (relative)
 */	
	public function getThumbUrl() {
		if ($this->_thumbUrl !== null)
			return $this->_thumbUrl;
			
		$this->_thumbUrl = $this->_getThumbUrl(false);
		return $this->_thumbUrl;
	}

/**
 * Returns full thumb url (incl base_url)
 */	
	public function getThumbUrlFull() {
		if ($this->_thumbUrlFull !== null)
			return $this->_thumbUrlFull;
			
		$this->_thumbUrlFull = $this->_getThumbUrl(true);
		return $this->_thumbUrlFull;
	}

/**
 * Generates url path from file path
 * 
 * @param boolean $full If TRUE, prepends base_url
 */	
	private function _getThumbUrl($full = true) {
		
		$thumbPath = $this->getThumbFilePath();
		if (!$thumbPath)
			return false;
			
		$thumbFile = basename($thumbPath);
		$wwwPath = $this->cache_www . $thumbFile;
		if ($full)
			$wwwPath = $this->base_url . $wwwPath;
		
		return $wwwPath;
	}
	
	
	/*
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
	*/

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
        
    	if (empty($this->_sourceFilePath) || !file_exists($this->_sourceFilePath)) {
    		$this->log("Could not render PhpThumb for '%s'.File not found.",$this->_sourceFilePath);
    		return false;
    	}
    	
    	$reset = false; 
    	
        //parse settings
        $this->_parseSettings();
        
        //render
        $this->phpThumb->setSourceFilename($this->_sourceFilePath);
        if ($renderToBrowser):
    		if($this->phpThumb->GenerateThumbnail()) {
    			#debug("generatedThumbnail");
		        if($this->phpThumb->RenderOutput()) {
	    			#debug("RenderOutput OK");
	    			#Configure::write('debug',0);
                	$this->phpThumb->OutputThumbnail();
                	#debug($this->phpThumb->debugmessages);
		        } else {
	    			debug("RenderOutput Failed");
					$this->log('RenderOuput() Failed:<pre>'.$this->phpThumb->fatalerror."\n\n".implode("\n\n", $this->phpThumb->debugmessages).'</pre>');	        	
		        }
            } else {
				$this->log('GenerateThumbnail() Failed:<pre>'.$this->phpThumb->fatalerror."\n\n".implode("\n\n", $this->phpThumb->debugmessages).'</pre>');
            }
        else:
        	#$path_info = pathinfo($this->_target);
        	#$cacheFilename = $path_info['basename'];
	        #$this->setParameter('cache_filename', $this->phpThumb->config_cache_directory.$cacheFilename);
	        //check cache
	        if(!is_file($this->phpThumb->cache_filename)){
	        	#$this->log('Thumbnail does not exist -> GenerateThumbnail()');
	            if($this->phpThumb->GenerateThumbnail()) {
	                if (!$this->phpThumb->RenderToFile($this->phpThumb->cache_filename)) {
						$this->log('RenderToFile() Failed:<pre>'.$this->phpThumb->fatalerror."\n\n".implode("\n\n", $this->phpThumb->debugmessages).'</pre>');
	                }
	            } else {
					// do something with debug/error messages
					$this->log('GenerateThumbnail() Failed:<pre>'.$this->phpThumb->fatalerror."\n\n".implode("\n\n", $this->phpThumb->debugmessages).'</pre>');
	            }
	        }
	        
	        if(is_file($this->phpThumb->cache_filename)){
	        	#$this->log("Found cached file ".$this->phpThumb->cache_filename);
	            return $this->phpThumb->cache_filename;
	        } else {
	        	$this->log(__("Rendering failed: %s not found",$this->phpThumb->cache_filename));
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
	    	debug('PhpThumbHelper:'.$msg);
    	}
    	CakeLog::write('phpthumb',$msg);
    }
}
?>