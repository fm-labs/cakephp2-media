<?php
App::uses('FileExplorer','Media.FileExplorer');

define('MEDIA_TEST_APP_ROOT',App::pluginPath('Media').'Test'.DS.'test_app'.DS);
define('FILEBROWSER_TEST_BASE_PATH',MEDIA_TEST_APP_ROOT.'webroot'.DS.'files'.DS);
define('FILEBROWSER_TEST_BASE_URL','/files/');

class FileExplorerTest extends CakeTestCase {
	
	public $testRoot = FILEBROWSER_TEST_BASE_PATH;
	
	public function setUp() {
		parent::setUp();
	}
	
	protected function &_getX($dir = null) {

		$x = new TestFileExplorer(array('basePath' => $this->testRoot));
		$x->dir($dir);
		
		return $x;
	}
	
	public function testStaticIsAbsolute() {
		
		$dirs = array(
			'/' => true,
			'/folderA' => true,
			'/folderA/' => true,
			'folderA' => false,
		);
		foreach($dirs as $dir => $expected) {
			$result = TestFileExplorer::isAbsolute($dir);
			$this->assertEqual($result, $expected);
		}	
	}

	public function testStaticIsSlashTerm() {
	
		$dirs = array(
				'/' => true,
				'/folderA' => false,
				'/folderA/' => true,
				'folderA' => false,
		);
		foreach($dirs as $dir => $expected) {
			$result = TestFileExplorer::isSlashTerm($dir);
			$this->assertEqual($result, $expected);
		}
	}
	
	public function testConstructor() {
		
		$x = $this->_getX();
		$this->assertEqual($x->__testGetBasePath(), $this->testRoot);
		
	}
	
	public function testDir() {
		
		// all of this directories should translate into expected dirpath
		$dirs = array(
			'folderA',
			'/folderA',
			'folderA/',
			'//folderA',
			'folderA//',
			' folderA ',
			'/folderB/../folderA/',
			'/folderA/../folderB/../folderA/',
			'/folderB/././../folderA/../folderB/./../folderA',
		);
		$expected = '/folderA/';
		
		foreach($dirs as $dir) {
			$x = $this->_getX($dir);
			$this->assertEqual($x->getDirPath(false), $expected);
		}
		
		// directory paths above root
		$dirs = array(
			'/../'
		);
		$expected = '/';
		
		foreach($dirs as $dir) {
			$x = $this->_getX($dir);
			$this->assertEqual($x->getDirPath(false), $expected);
		}
		
		// accessing a non-existant directory throws exception
		$this->expectException('Exception');
		$x = $this->_getX('/non-existant-dir/');
		
	}
	
	public function testStaticParentDir() {
		
		$dirs = array(
				'folderA' => '/',
				'/folderA' => '/',
				'folderA/' => '/',
				// '//folderA' => '/',
				'folderA//' => '/',
				' folderA ' => '/',
				'/folderB/../folderA/' => '/',
				'/folderA/../folderB/../folderA/' => '/',
				'/folderB/././../folderA/../folderB/./../folderA' => '/',
				'/../' => '/',
				'/../.././..' => '/',
				'/folderA/folderB' => '/folderA/',
				'/folderA/folderB/folderC' => '/folderA/folderB/',
				'/folderA/folderB/folderC/folderD/' => '/folderA/folderB/folderC/',
		);
		
		foreach($dirs as $dir => $expected) {
			//debug($dir. " - ". $expected);
			$this->assertEqual(TestFileExplorer::parentDir($dir), $expected);
		}
	}
	
	public function testStaticInPath() {
		
		$paths = array(
			array('/folderA','/folderA/test',true),
			array('/folderA','/folderB',false),		
		);
		foreach($paths as $path) {
			list($path1,$path2,$expected) = $path;
			$this->assertEqual(TestFileExplorer::inPath($path1, $path2), $expected);
		}
	}
	
	public function testgetContents() {
		
		$result = $this->_getX()->dir('/folderA')->getContents();
		$expected = array(
			'Folder' => array(),
			'File' => array(
				(int) 0 => array(
					'name' => '',
		            'basename' => '.file',
		            'size' => 0,
		            'ext' => 'file',
		            'permissions' => 33204,
		            'writeable' => false,
				),
				(int) 1 => array(
					'basename' => 'file.txt',
					'name' => 'file',
		            'size' => 0,
		            'ext' => 'txt',
		            'permissions' => 33204,
		            'writeable' => false,
				),
				(int) 2 => array(
					'basename' => 'file1',
					'name' => 'file1',
		            'size' => 19,
		            'ext' => null,
		            'permissions' => 33188,
		            'writeable' => false,
				)
			)
		);
		$this->assertEqual($result, $expected);
	}
	
	public function testToArray() {
		
		// contents not loaded
		$result = $this->_getX()->dir('/folderA')->toArray();
		$this->assertEqual($result['dir'],'/folderA/');
		$this->assertEqual($result['parent_dir'],'/');
		
		// contents loaded
		
		// with auto-load contents
		
	}
	
	public function tearDown() {
		parent::tearDown();
	}
	
}

class TestFileExplorer extends FileExplorer {
	
	public function __testGetBasePath() {
		return $this->basePath;
	}
}