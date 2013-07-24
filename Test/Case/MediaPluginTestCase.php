<?php
defined('MEDIA_TESTAPP_ROOT') 
	or define('MEDIA_TESTAPP_ROOT', CakePlugin::path('Media') . 'Test'.DS.'test_app'.DS );
	
defined('MEDIA_TESTAPP_DUMMYDIR') 
	or define('MEDIA_TESTAPP_DUMMYDIR', MEDIA_TESTAPP_ROOT.'dummyfiles'.DS);
	
defined('MEDIA_TESTAPP_UPLOADDIR') 
	or define('MEDIA_TESTAPP_UPLOADDIR',TMP.'tests'.DS.'media_upload'.DS);

class MediaPluginTestCase extends CakeTestCase {
	
}