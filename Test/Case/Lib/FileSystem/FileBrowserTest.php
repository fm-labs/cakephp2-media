<?php
App::uses('FileBrowser','Media.FileSystem');

define('MEDIA_TEST_APP_ROOT',App::pluginPath('Media').'Test'.DS.'test_app'.DS);
define('FILEBROWSER_TEST_BASE_PATH',MEDIA_TEST_APP_ROOT.'webroot'.DS.'files'.DS);
define('FILEBROWSER_TEST_BASE_URL','/files/');

class FileBrowserTest extends CakeTestCase {

	public $testConfig = array(
		'basePath' => FILEBROWSER_TEST_BASE_PATH,
		'baseUrl' => FILEBROWSER_TEST_BASE_URL		
	);
	
	public function setUp() {
		parent::setUp();
		
		Configure::write('Media.FileBrowser.test',$this->testConfig);
	}
	
	public function testConfig() {
		
		$FileBrowser = new TestFileBrowser();
		//array
		$config = array('basePath'=>ROOT,'baseUrl'=>'/');
		$FileBrowser->config($config);
		
		$this->assertEqual($FileBrowser->config(), $config);

		//config
		$FileBrowser->config('test');
		
		$result = $FileBrowser->config();
		$this->assertEqual($result, $this->testConfig);
	}
	
	public function testSetPath() {

		$FileBrowser = new TestFileBrowser('test');
		
		//top level
		$FileBrowser->setPath('/');
		$this->assertEqual($FileBrowser->path(), '');
		$this->assertEqual($FileBrowser->dir(), '');
		
		//2nd level
		$FileBrowser->setPath('/folderA/');
		$this->assertEqual($FileBrowser->path(), 'folderA/');
		$this->assertEqual($FileBrowser->dir(), 'folderA');

		//3rd level
		$FileBrowser->setPath('/folderB/folderB1/');
		$this->assertEqual($FileBrowser->path(), 'folderB/folderB1/');
		$this->assertEqual($FileBrowser->dir(), 'folderB1');
		
		//back to top level
		$FileBrowser->setPath('/');
		$this->assertEqual($FileBrowser->path(), '');
		
		//2nd level (relative)
		$FileBrowser->setPath('folderB/');
		$this->assertEqual($FileBrowser->path(), 'folderB/');
		$this->assertEqual($FileBrowser->dir(), 'folderB');
		
		//3rd level (relative)
		$FileBrowser->setPath('folderB1/');
		$this->assertEqual($FileBrowser->path(), 'folderB/folderB1/');
		$this->assertEqual($FileBrowser->dir(), 'folderB1');
		
		//go to parent (2nd)
		$FileBrowser->setPath('..');
		$this->assertEqual($FileBrowser->path(), 'folderB/');
		
		//go to parent (top)
		$FileBrowser->setPath('..');
		$this->assertEqual($FileBrowser->path(), '');
		
		//go to parent - MUST FAIL
		$FileBrowser->setPath('..');
		$this->assertEqual($FileBrowser->path(), '');
		
		//down one more time
		$FileBrowser->setPath('folderC/');
		$this->assertEqual($FileBrowser->path(), 'folderC/');

		//reload dir
		$FileBrowser->setPath('.');
		$this->assertEqual($FileBrowser->path(), 'folderC/');
		
		//back to top
		$FileBrowser->setPath('..');
		$this->assertEqual($FileBrowser->path(), '');
		
		//file paths
		$FileBrowser->setPath('/folderA/file1'); //enter path and select file
		$this->assertEqual($FileBrowser->path(), 'folderA/');
		$this->assertEqual($FileBrowser->dir(), 'folderA');
		$this->assertEqual($FileBrowser->file(), 'file1');
		
		$FileBrowser->setPath('/folderB/folderB1/fileb1'); //enter path and select file
		$this->assertEqual($FileBrowser->path(), 'folderB/folderB1/');
		$this->assertEqual($FileBrowser->dir(), 'folderB1');
		$this->assertEqual($FileBrowser->file(), 'fileb1');
		
		//back to top
		$FileBrowser->setPath('/');
		$this->assertEqual($FileBrowser->path(), '');
		
		$FileBrowser->setPath('folderA/file2');
		$this->assertEqual($FileBrowser->path(), 'folderA/');
		$this->assertEqual($FileBrowser->dir(), 'folderA');
		$this->assertEqual($FileBrowser->file(), 'file2');
	}
	
	public function tearDown() {
		parent::tearDown();
	}
	
}

class TestfileBrowser extends FileBrowser {
	
}