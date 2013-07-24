<?php
App::uses('MediaTools','Media.Lib');

class MediaToolsTest extends CakeTestCase {
	/**
	 * Test TestMediaTools::splitBasename()
	 */
	public function testSplitBasename() {
	
		$result = TestMediaTools::splitBasename('file.txt');
		$this->assertEqual($result, array('file','txt','.txt'));
	
		$result = TestMediaTools::splitBasename('.htaccess');
		$this->assertEqual($result, array('','htaccess','.htaccess'));
	
		$result = TestMediaTools::splitBasename('file');
		$this->assertEqual($result, array('file',null,null));
	}
	
	/**
	 * Test TestMediaTools::validateMimeType()
	 */
	public function testValidateMimeType() {
	
		$result = TestMediaTools::validateMimeType('image/png','*');
		$this->assertTrue($result);
	
		$result = TestMediaTools::validateMimeType('image/png',array('image/png','image/jpg'));
		$this->assertTrue($result);
	
		$result = TestMediaTools::validateMimeType('image/png',array('image/*'));
		$this->assertTrue($result);
	
		$result = TestMediaTools::validateMimeType('image/gif',array('image/png','image/jpg'));
		$this->assertFalse($result);
	
		$result = TestMediaTools::validateMimeType('text/plain',array('image/*'));
		$this->assertFalse($result);
	
		$result = TestMediaTools::validateMimeType('image/gif',array());
		$this->assertFalse($result);
	}
	
	/**
	 * Test TestMediaTools::validateFileExtension()
	 */
	public function testValidateFileExtension() {
	
		$result = TestMediaTools::validateFileExtension('png','*');
		$this->assertTrue($result);
	
		$result = TestMediaTools::validateFileExtension('png','png');
		$this->assertTrue($result);
	
		$result = TestMediaTools::validateFileExtension('png',array('png','jpg'));
		$this->assertTrue($result);
	
		$result = TestMediaTools::validateFileExtension('gif',array('png','jpg'));
		$this->assertFalse($result);
	
		$result = TestMediaTools::validateFileExtension('png',array());
		$this->assertFalse($result);
	}	
}

class TestMediaTools extends MediaTools {
	
}