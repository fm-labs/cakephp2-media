<?php
require_once(dirname(__DIR__) . DS . 'MediaPluginTestCase.php');

App::uses('MediaUploader','Media.Lib');
App::uses('Folder','Utility');

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
		$this->UploadFolder = new Folder(MEDIA_TESTAPP_UPLOADDIR,true,0777);
		
		// setup dummy upload files
		$this->upload1 = array(
				'name' => 'Upload File 1.txt',
				'type' => 'text/plain',
				'tmp_name' => $this->dummyDir.'upload1.txt',
				'error' => (int) 0,
				'size' => @filesize($this->dummyDir.'upload1.txt')
		);
	
		$this->upload2 = array(
				'name' => 'Upload File 2.txt',
				'type' => 'text/plain',
				'tmp_name' => $this->dummyDir.'upload2.txt',
				'error' => (int) 0,
				'size' => @filesize($this->dummyDir.'upload2.txt')
		);
	
		$this->uploadNoExt = array(
				'name' => 'Upload_Without_Ext',
				'type' => 'text/plain',
				'tmp_name' => $this->dummyDir.'upload_noext',
				'error' => (int) 0,
				'size' => @filesize($this->dummyDir.'upload_noext')
		);
	
		$this->uploadImage = array(
				'name' => 'Upload.jpg',
				'type' => 'image/jpg',
				'tmp_name' => $this->dummyDir.'upload.jpg',
				'error' => (int) 0,
				'size' => @filesize($this->dummyDir.'upload.jpg')
		);
		
		$this->uploadEmpty = array(
				'name' => 'Upload_empty.txt',
				'type' => 'text/plain',
				'tmp_name' => $this->dummyDir.'upload_empty.txt',
				'error' => (int) 0,
				'size' => @filesize($this->dummyDir.'upload_empty.txt')
		);
	
	}
	
	/**
	 * Check if all dummy files exist
	 */
	public function testTestSetup() {
		
		// check dummy files
		foreach(array($this->upload1,$this->upload2,$this->uploadNoExt,$this->uploadImage) as $upload) {
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
			'multiple' => false,
			'minFileSize' => (int) 1,
			'maxFileSize' => (int) 2000000,
			'allowedMimeType' => '*',
			'allowedFileExtension' => '*',
			'slug' => '_',
			'hashFilename' => false,
			'allowOverwrite' => false
		);
		$this->assertEqual($result, $expected);
	}
	
	public function testSetMinFileSize() {
		$Uploader = new TestMediaUploader();
		$Uploader->setMinFileSize(999);
		
		$result = $Uploader->getConfig();
		$this->assertEqual($result['minFileSize'],999);
	}

	public function testSetMaxFileSize() {
		$Uploader = new TestMediaUploader();
		$Uploader->setMaxFileSize(999);
	
		$result = $Uploader->getConfig();
		$this->assertEqual($result['maxFileSize'],999);
	}
	
	public function testSetAllowedMimeType() {
		$Uploader = new TestMediaUploader();
		$Uploader->setAllowedMimeType(array('image/*'));
	
		$result = $Uploader->getConfig();
		$this->assertEqual($result['allowedMimeType'],array('image/*'));
	}	

	public function testSetAllowedFileExtension() {
		$Uploader = new TestMediaUploader();
		$Uploader->setAllowedFileExtension(array('jpg','png'));
	
		$result = $Uploader->getConfig();
		$this->assertEqual($result['allowedFileExtension'],array('jpg','png'));
	}
	
	
	public function testUploadWithFormSizeExceededUploadError() {
	
		$upload = array(
			'error' => UPLOAD_ERR_FORM_SIZE,
		);
		$this->expectException('UploadException','Maximum form file size exceeded');
		
		$Uploader = new TestMediaUploader($upload);
		$result = $Uploader->upload();
	}
	
	public function testUploadWithNoFileUploadError() {
	
		$upload = array(
			'error' => UPLOAD_ERR_NO_FILE,
		);
		$this->expectException('UploadException','No file uploaded');
		
		$Uploader = new TestMediaUploader($upload);
		$result = $Uploader->upload();
	}
	
	public function testUploadWithPartialUploadError() {
	
		$upload = array(
				'error' => UPLOAD_ERR_PARTIAL,
		);
		$this->expectException('UploadException','File only partially uploaded');
		
		$Uploader = new TestMediaUploader($upload);
		$result = $Uploader->upload();
	}
	
	public function testUploadWithMinFileSizeError() {
	
		// pre-condition
		$this->assertTrue($this->uploadEmpty['size'] === 0);
		
		$this->expectException('UploadException','Minimum file size error');
		
		$Uploader = new TestMediaUploader($this->uploadEmpty);
		$Uploader->setMinFileSize(1);
		$result = $Uploader->upload();
	}
	
	public function testUploadWithMaxFileSizeError() {
	
		$this->expectException('UploadException','Maximum file size exceeded');
		
		$Uploader = new TestMediaUploader($this->upload1);
		$Uploader->setMaxFileSize(1);
		$result = $Uploader->upload();
	}
	
	public function testUploadWithMimeTypeError() {
	
		$this->expectException('UploadException','Invalid mime type');
		
		$Uploader = new TestMediaUploader($this->upload1);
		$Uploader->setAllowedMimeType(array('image/*'));
		$result = $Uploader->upload();
	}
	
	public function testUploadWithFileExtensionError() {
	
		$this->expectException('UploadException','Invalid file extension');
		
		$Uploader = new TestMediaUploader($this->upload1);
		$Uploader->setAllowedFileExtension(array('jpg','png'));
		$result = $Uploader->upload();
	}
	
	
	public function testZeroConfigUpload() {
		
		$Uploader = new TestMediaUploader($this->upload1);
		$result = $Uploader->upload();
		
		$this->assertTrue(file_exists($result['path']));
		$this->assertEqual($result['ext'], 'txt');
		$this->assertEqual($result['dotExt'], '.txt');
		$this->assertEqual(preg_match('/Upload_File_1_([0-9a-z]+).txt$/',$result['basename']), 1);
		$this->assertEqual(preg_match('/Upload_File_1_([0-9a-z]+)$/',$result['filename']), 1);
		debug($result);
	}
	
	public function tearDown() {
		parent::tearDown();
		
		// clean up test upload dir
		$this->UploadFolder->delete();
		unset($this->UploadFolder);
	}
}

class TestMediaUploader extends MediaUploader {
	
	public function __construct($data = array()) {
		parent::__construct($data);
		$this->setUploadDir(MEDIA_TESTAPP_UPLOADDIR);
	}
	
}