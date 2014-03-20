*** MEDIA PLUGIN ***

1. INSTALL
2. CONFIGURE
3. USAGE


*** 1. INSTALL ***

git submodule add [url_to_repo] [path/to/plugins/]Media

or 

- create a new folder 'Media' in your application's plugin directory
- copy all files to that 'Media' directory

*** 2. CONFIGURE ***


2.1 Enable the plugin

To enable the Media plugin in your application
add this to your app/Config/bootstrap.php

CakePlugin::load('Media')


2.2 Paths

/**
 * Media Data directory
 */
defined('MEDIA_DATA_DIR') or define('MEDIA_DATA_DIR', ROOT.DS.'media'.DS);

/**
 * Media Data directory
 */
defined('MEDIA_DATA_URL') or define('MEDIA_DATA_URL', '/m/');

/**
 * Media Attachment directory
 */
defined('MEDIA_ATTACHMENT_DIR') or define('MEDIA_ATTACHMENT_DIR', MEDIA_DATA_DIR . 'attachments' . DS);

/**
 * Media cache directory
 */
defined('MEDIA_CACHE_DIR') or define('MEDIA_CACHE_DIR', CACHE . "media" . DS);

/**
 * Upload directory
 */
defined('MEDIA_UPLOAD_DIR') or define('MEDIA_UPLOAD_DIR', TMP . "uploads" . DS);

/**
 * Media thumbnail directory
 */
defined('MEDIA_THUMB_DIR') or define('MEDIA_THUMB_DIR',WWW_ROOT."cache".DS);
defined('MEDIA_THUMB_URL') or define('MEDIA_THUMB_URL', '/cache/');



*** 3. USAGE ***

3.1 FileExplorer


3.2 Uploads


3.3 Attachments


3.4 Thumbnails

