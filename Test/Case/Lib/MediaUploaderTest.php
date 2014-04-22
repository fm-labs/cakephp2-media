<?php
require_once (dirname(__DIR__) . DS . 'MediaPluginTestCase.php');

App::uses('MediaUploader', 'Media.Lib');
App::uses('Folder', 'Utility');

class MediaUploaderTest extends MediaPluginTestCase {

/**
 * Directory with dummy upload files
 *
 * @var string
 */
	public $dummyDir = MEDIA_TESTAPP_DUMMYDIR;

/**
 * Folder instance of the test upload directory
 * @var Folder
 */
	public $UploadFolder;

	public function setUp() {
		parent::setUp();

		// setup test upload dir
		$this->UploadFolder = new Folder(MEDIA_TESTAPP_UPLOADDIR, true, 0777);

		// setup dummy upload files
		$this->upload1 = array(
				'name' => 'Upload File 1.txt',
				'type' => 'text/plain',
				'tmp_name' => $this->dummyDir . 'upload1.txt',
				'error' => (int)0,
				'size' => @filesize($this->dummyDir . 'upload1.txt')
		);

		$this->upload2 = array(
				'name' => 'Upload File 2.txt',
				'type' => 'text/plain',
				'tmp_name' => $this->dummyDir . 'upload2.txt',
				'error' => (int)0,
				'size' => @filesize($this->dummyDir . 'upload2.txt')
		);

		$this->uploadNoExt = array(
				'name' => 'Upload_Without_Ext',
				'type' => 'text/plain',
				'tmp_name' => $this->dummyDir . 'upload_noext',
				'error' => (int)0,
				'size' => @filesize($this->dummyDir . 'upload_noext')
		);

		$this->uploadImage = array(
				'name' => 'Upload.jpg',
				'type' => 'image/jpg',
				'tmp_name' => $this->dummyDir . 'upload.jpg',
				'error' => (int)0,
				'size' => @filesize($this->dummyDir . 'upload.jpg')
		);

		$this->uploadEmpty = array(
				'name' => 'Upload_empty.txt',
				'type' => 'text/plain',
				'tmp_name' => $this->dummyDir . 'upload_empty.txt',
				'error' => (int)0,
				'size' => @filesize($this->dummyDir . 'upload_empty.txt')
		);
	}

/**
 * Check if all dummy files exist
 */
	public function testTestSetup() {
		// check dummy files
		foreach (array($this->upload1, $this->upload2, $this->uploadNoExt, $this->uploadImage) as $upload) {
			$this->assertTrue(file_exists($upload['tmp_name']));
		}

		// check upload dir
		$this->assertEqual($this->UploadFolder->pwd(), MEDIA_TESTAPP_UPLOADDIR);
	}

	public function testDefaultConfig() {
		$Uploader = new TestMediaUploader();
		$result = $Uploader->getConfig();
		$expected = array(
			'uploadDir' => MEDIA_TESTAPP_UPLOADDIR,
			'minFilesize' => (int)1,
			'maxFilesize' => (int)2 * 1024 * 1024,
			'multiple' => false,
			'mimeTypes' => '*',
			'fileExtensions' => '*',
			'slug' => '_',
			//'pattern' => '',
			'hashFilename' => false,
			'uniqueFilename' => true,
			'overwrite' => false,
			'filename' => null
		);
		$this->assertEqual($result, $expected);
	}

	public function testSetMinFilesize() {
		$Uploader = new TestMediaUploader();
		$Uploader->setMinFilesize(999);

		$result = $Uploader->getConfig();
		$this->assertEqual($result['minFilesize'], 999);
	}

	public function testSetMaxFilesize() {
		$Uploader = new TestMediaUploader();
		$Uploader->setMaxFilesize(999);

		$result = $Uploader->getConfig();
		$this->assertEqual($result['maxFilesize'], 999);
	}

	public function testSetmimeTypes() {
		$Uploader = new TestMediaUploader();
		$Uploader->setmimeTypes(array('image/*'));

		$result = $Uploader->getConfig();
		$this->assertEqual($result['mimeTypes'], array('image/*'));
	}

	public function testSetfileExtensions() {
		$Uploader = new TestMediaUploader();
		$Uploader->setfileExtensions(array('jpg', 'png'));

		$result = $Uploader->getConfig();
		$this->assertEqual($result['fileExtensions'], array('jpg', 'png'));
	}

	public function testUploadWithFormSizeExceededUploadError() {
		$upload = array(
			'error' => UPLOAD_ERR_FORM_SIZE,
		);

		$Uploader = new TestMediaUploader($upload);
		$result = $Uploader->upload();

		$this->assertTrue(isset($result['upload_err']));
		$this->assertEqual($result['upload_err'], 'Maximum form file size exceeded');
	}

	public function testUploadWithNoFileUploadError() {
		$upload = array(
			'error' => UPLOAD_ERR_NO_FILE,
		);

		$Uploader = new TestMediaUploader($upload);
		$result = $Uploader->upload();

		$this->assertTrue(isset($result['upload_err']));
		$this->assertEqual($result['upload_err'], 'No file uploaded');
	}

	public function testUploadWithPartialUploadError() {
		$upload = array(
				'error' => UPLOAD_ERR_PARTIAL,
		);

		$Uploader = new TestMediaUploader($upload);
		$result = $Uploader->upload();

		$this->assertTrue(isset($result['upload_err']));
		$this->assertEqual($result['upload_err'], 'File only partially uploaded');
	}
	
	public function testUploadWithMinFilesizeError() {
		// pre-condition
		$this->assertTrue($this->uploadEmpty['size'] === 0);

		$Uploader = new TestMediaUploader($this->uploadEmpty);
		$Uploader->setMinFilesize(1);
		$result = $Uploader->upload();

		$this->assertTrue(isset($result['upload_err']));
		$this->assertEqual($result['upload_err'], 'Minimum file size error');
	}

	public function testUploadWithMaxFilesizeError() {
		$Uploader = new TestMediaUploader($this->upload1);
		$Uploader->setMaxFilesize(1);
		$result = $Uploader->upload();

		$this->assertTrue(isset($result['upload_err']));
		$this->assertEqual($result['upload_err'], 'Maximum file size exceeded');
	}

	public function testUploadWithMimeTypeError() {
		$Uploader = new TestMediaUploader($this->upload1);
		$Uploader->setmimeTypes(array('image/*'));
		$result = $Uploader->upload();

		$this->assertTrue(isset($result['upload_err']));
		$this->assertEqual($result['upload_err'], 'Invalid mime type');
	}
	
	public function testUploadWithFileExtensionError() {
		$Uploader = new TestMediaUploader($this->upload1);
		$Uploader->setfileExtensions(array('jpg', 'png'));
		$result = $Uploader->upload();

		$this->assertTrue(isset($result['upload_err']));
		$this->assertEqual($result['upload_err'], 'Invalid file extension');
	}

	public function testZeroConfigUpload() {
		$Uploader = new TestMediaUploader($this->upload1);
		$result = $Uploader->upload();

		$this->assertTrue(file_exists($result['path']));
		$this->assertEqual($result['ext'], 'txt');
		$this->assertEqual($result['dotExt'], '.txt');
		$this->assertEqual(preg_match('/Upload_File_1_([0-9a-z]+).txt$/', $result['basename']), 1);
		$this->assertEqual(preg_match('/Upload_File_1_([0-9a-z]+)$/', $result['filename']), 1);
	}

	public function testUploadOverwrite() {
		$config = array(
			'overwrite' => true,
			'uniqueFilename' => false,
			'hashFilename' => false,
		);
		$Uploader = new TestMediaUploader($this->upload1, $config);
		$result = $Uploader->upload();
		$this->assertInternalType('array', $result);

		$Uploader = new TestMediaUploader($this->upload1, $config);
		$result = $Uploader->upload();
		$this->assertInternalType('array', $result);
	}

	public function testUploadMultiple() {
		$config = array(
			'multiple' => true,
			'overwrite' => false,
			'uniqueFilename' => false,
			'hashFilename' => false,
		);
		$Uploader = new TestMediaUploader(array($this->upload1, $this->upload2), $config);
		$result = $Uploader->upload();

		$this->assertInternalType('array', $result);
		$this->assertTrue(isset($result[0]));
		$this->assertTrue(isset($result[1]));
	}

	public function testUploadMultipleError() {
		$config = array(
			'multiple' => true,
			'overwrite' => false,
			'uniqueFilename' => false,
			'hashFilename' => false,
			'minFilesize' => 1 * 1024 * 1024
		);
		$Uploader = new TestMediaUploader(array($this->upload1, $this->upload2), $config);
		$result = $Uploader->upload();

		$this->assertInternalType('array', $result);
		$this->assertTrue(isset($result[0]));
		$this->assertTrue(isset($result[1]));
	}

	public function testUploadWithPredefinedFilename() {
		$config = array(
			'multiple' => false,
			'overwrite' => false,
			'uniqueFilename' => false,
			'hashFilename' => false,
			'filename' => 'test.file'
		);
		$Uploader = new TestMediaUploader($this->upload1, $config);
		$result = $Uploader->upload();

		$this->assertTrue(file_exists($result['path']));
		$this->assertEqual($result['ext'], 'file');
		$this->assertEqual($result['dotExt'], '.file');
		$this->assertEqual($result['basename'], 'test.file');
	}

	public function tearDown() {
		parent::tearDown();

		// clean up test upload dir
		$this->UploadFolder->delete();
		unset($this->UploadFolder);
	}

}

class TestMediaUploader extends MediaUploader {

	public function __construct($data = array(), $config = array()) {
		parent::__construct($data, $config);
		$this->setUploadDir(MEDIA_TESTAPP_UPLOADDIR);
	}

}