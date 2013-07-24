<?php
/**
 * Media Base Data dir
 */
defined('MEDIA_DATA_DIR') or define('MEDIA_DATA_DIR', ROOT.DS.'media'.DS);
/**
 * Upload directory (full path)
 */
defined('MEDIA_UPLOAD_DIR') or define('MEDIA_UPLOAD_DIR', MEDIA_DATA_DIR . "uploads" . DS);

/**
 * Upload cache directory
 */
defined('MEDIA_CACHE_DIR') or define('MEDIA_CACHE_DIR', CACHE . "media" . DS);

/**
 * PhpThumb directories
 * @todo review / to many dirs
 */
defined('MEDIA_THUMB_DIR') or define('MEDIA_THUMB_DIR',WWW_ROOT."cache".DS);
defined('MEDIA_THUMB_URL') or define('MEDIA_THUMB_URL', '/cache/');

/**
 * @deprecated
 */
defined('MEDIA_THUMB_TMP_DIR') or define('MEDIA_THUMB_TMP_DIR',TMP."phpthumb".DS);
/**
 * @deprecated
 */ 	
defined('MEDIA_THUMB_CACHE_DIR') or define('MEDIA_THUMB_CACHE_DIR', IMAGES . "cache" . DS);
#defined('MEDIA_THUMB_CACHE_DIR') or define('MEDIA_THUMB_CACHE_DIR',MEDIA_THUMB_TMP_DIR."cache".DS); 	


/**
 * Temporary upload directory for attachments
 * @deprecated
 */
defined('MEDIA_UPLOAD_TMP_DIR') or define('MEDIA_UPLOAD_TMP_DIR', TMP . "media" . DS);

/**
 * Media bootstrap flag
 * @deprecated
 */
@define('MEDIA_BOOTSTRAP',true); //TODO check if this is still needed

?>