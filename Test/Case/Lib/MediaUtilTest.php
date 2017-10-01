<?php
App::uses('MediaUtil', 'Media.Lib');

class MediaUtilTest extends CakeTestCase {
	
/**
 * Test TestMediaUtil::splitBasename()
 */
	public function testSplitBasename() {
		$result = TestMediaUtil::splitBasename('file.txt');
		$this->assertEqual($result, array('file', 'txt', '.txt'));

		$result = TestMediaUtil::splitBasename('.htaccess');
		$this->assertEqual($result, array('', 'htaccess', '.htaccess'));

		$result = TestMediaUtil::splitBasename('file');
		$this->assertEqual($result, array('file', null, null));
	}

/**
 * Test TestMediaUtil::validateMimeType()
 */
	public function testValidateMimeType() {
		$result = TestMediaUtil::validateMimeType('image/png', '*');
		$this->assertTrue($result);

		$result = TestMediaUtil::validateMimeType('image/png', array('image/png', 'image/jpg'));
		$this->assertTrue($result);

		$result = TestMediaUtil::validateMimeType('image/png', array('image/*'));
		$this->assertTrue($result);

		$result = TestMediaUtil::validateMimeType('image/gif', array('image/png', 'image/jpg'));
		$this->assertFalse($result);

		$result = TestMediaUtil::validateMimeType('text/plain', array('image/*'));
		$this->assertFalse($result);

		$result = TestMediaUtil::validateMimeType('image/gif', array());
		$this->assertFalse($result);
	}

/**
 * Test TestMediaUtil::validateFileExtension()
 */
	public function testValidateFileExtension() {
		$result = TestMediaUtil::validateFileExtension('png', '*');
		$this->assertTrue($result);

		$result = TestMediaUtil::validateFileExtension('png', 'png');
		$this->assertTrue($result);

		$result = TestMediaUtil::validateFileExtension('png', array('png', 'jpg'));
		$this->assertTrue($result);

		$result = TestMediaUtil::validateFileExtension('gif', array('png', 'jpg'));
		$this->assertFalse($result);

		$result = TestMediaUtil::validateFileExtension('png', array());
		$this->assertFalse($result);
	}

}

class TestMediaUtil extends MediaUtil {

}