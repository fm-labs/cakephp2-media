<?php
if (!defined('PHPTHUMB_CACHE_DIR')) {
	define('PHPTHUMB_CACHE_DIR',IMAGES."cache".DS);
	//debug(__("FileManager: %s is not defined. Using default: '%s'",'PHPTHUMB_CACHE_ROOT',PHPTHUMB_CACHE_ROOT));
}
	
if (!defined('PHPTHUMB_CACHE_WWW')) {
	define('PHPTHUMB_CACHE_WWW', '/'.IMAGES_URL."cache/");
	//debug(__("FileManager: %s is not defined. Using default: '%s'",'PHPTHUMB_CACHE_WWW',PHPTHUMB_CACHE_WWW));
}

if (!defined('PHPTHUMB_TEMP_DIR')) {
	define('PHPTHUMB_TEMP_DIR',TMP."phpthumb".DS);
	//debug(__("FileManager: %s is not defined. Using default: '%s'",'PHPTHUMB_TEMP_DIR',PHPTHUMB_TEMP_DIR));
}	

@define('MEDIA_BOOTSTRAP',true);

?>