<?php
define('MEDIA_TMP_UPLOAD_DIR', CakePlugin::path('Media') . 'Test/test_app/tmp/attachments/' );
define('MEDIA_DEFAULT_UPLOAD_DIR', CakePlugin::path('Media') . 'Test/test_app/webroot/attachments/' );

App::uses('AttachableBehavior', 'Media.Model/Behavior');

class AttachableBehaviorTest extends CakeTestCase {
	
	/**
	 * @var array
	 */
	public $fixtures = array('plugin.media.attach');
	
	/**
	 * @var Model
	 */
	public $Attach;
	
	/**
	 * Attachable dir path with trailing DS
	 * @var string
	 */
	
	public $attachmentDir = MEDIA_DEFAULT_UPLOAD_DIR;
	public $tmpDir = MEDIA_TMP_UPLOAD_DIR;
	
	protected $file1;
	protected $file2;
	
	public function setUp() {
		parent::setUp();
		
		$this->upload1 = array(
			'name' => 'Upload File 1.txt',
			'type' => 'text/plain',
			'tmp_name' => $this->tmpDir.'upload1.txt',
			'error' => (int) 0,
			'size' => filesize($this->tmpDir.'upload1.txt')
		);

		$this->upload2 = array(
				'name' => 'Upload File 2.txt',
				'type' => 'text/plain',
				'tmp_name' => $this->tmpDir.'upload2.txt',
				'error' => (int) 0,
				'size' => filesize($this->tmpDir.'upload2.txt')
		);
		
		$this->Attach = ClassRegistry::init('Attach');
		
	}
	
	protected function _setupDefault() {
		
		$this->Attach->attachments = array(
			'file' => array(
					'dir' => $this->attachmentDir,
					'multiple' => false,
			),
			'files' => array(
					'dir' => $this->attachmentDir,
					'multiple' => true,
			)
		);
		$this->Attach->Behaviors->load('Media.Attachable',array());
	}
	
	public function testSplitBasename() {
		$Behavior = new TestAttachableBehavior();
		
		$result = $Behavior->_splitBasename('file.txt');
		$this->assertEqual($result, array('file','txt'));
		
		$result = $Behavior->_splitBasename('.htaccess');
		$this->assertEqual($result, array('','htaccess'));

		$result = $Behavior->_splitBasename('file');
		$this->assertEqual($result, array('file',null));
	}
	
	
	public function testValidateMimeType() {
		$Behavior = new TestAttachableBehavior();

		$result = $Behavior->_validateMimeType('image/png','*');
		$this->assertTrue($result);
		
		$result = $Behavior->_validateMimeType('image/png',array('image/png','image/jpg'));
		$this->assertTrue($result);

		$result = $Behavior->_validateMimeType('image/png',array('image/*'));
		$this->assertTrue($result);

		$result = $Behavior->_validateMimeType('image/gif',array('image/png','image/jpg'));
		$this->assertFalse($result);
		
		$result = $Behavior->_validateMimeType('image/gif',array());
		$this->assertFalse($result);
	}
	

	public function testValidateFileExtension() {
		$Behavior = new TestAttachableBehavior();

		$result = $Behavior->_validateFileExtension('png','*');
		$this->assertTrue($result);

		$result = $Behavior->_validateFileExtension('png','png');
		$this->assertTrue($result);
		
		$result = $Behavior->_validateFileExtension('png',array('png','jpg'));
		$this->assertTrue($result);
	
		$result = $Behavior->_validateFileExtension('gif',array('png','jpg'));
		$this->assertFalse($result);
	
		$result = $Behavior->_validateFileExtension('png',array());
		$this->assertFalse($result);
	}
	
	public function testValidateFileName() {
		//TODO testValidateFileName()
	}
	
	public function testUploadWithFormSizeExceededUploadError() {

		$Behavior = new TestAttachableBehavior();
		
		$upload = array(
			'error' => UPLOAD_ERR_FORM_SIZE,
		);
		$this->expectException('AttachableUploadException','Maximum form file size exceeded');
		$result = $Behavior->_upload($upload, array());
	}
	
	public function testUploadWithNoFileUploadError() {

		$Behavior = new TestAttachableBehavior();
		
		$upload = array(
			'error' => UPLOAD_ERR_NO_FILE,
		);
		$this->expectException('AttachableUploadException','No file uploaded');
		$result = $Behavior->_upload($upload, array());
	}
	
	public function testUploadWithPartialUploadError() {

		$Behavior = new TestAttachableBehavior();
		
		$upload = array(
			'error' => UPLOAD_ERR_PARTIAL,
		);
		$this->expectException('AttachableUploadException','File only partially uploaded');
		$result = $Behavior->_upload($upload, array());
	}

	public function testUploadWithMinFileSizeError() {
	
		$Behavior = new TestAttachableBehavior();
	
		$upload = am($this->upload1,array( 'size' => 0 ));
		$config = am($Behavior->defaultSettings, array('minFileSize' => 1));
		$this->expectException('AttachableUploadException','Minimum file size error');
		$result = $Behavior->_upload($upload, $config);
	}

	public function testUploadWithMaxFileSizeError() {
	
		$Behavior = new TestAttachableBehavior();
	
		$upload = am($this->upload1,array( 'size' => 2 ));
		$config = am($Behavior->defaultSettings, array('maxFileSize' => 1));
		$this->expectException('AttachableUploadException','Maximum file size exceeded');
		$result = $Behavior->_upload($upload, $config);
	}	

	public function testUploadWithMimeTypeError() {
	
		$Behavior = new TestAttachableBehavior();

		$upload = am($this->upload1,array( 'type' => 'text/plain' ));
		$config = am($Behavior->defaultSettings, array('allowedMimeType' => 'image/jpg'));
		$this->expectException('AttachableUploadException','Invalid mime type');
		$result = $Behavior->_upload($upload, $config);
	}

	public function testUploadWithFileExtensionError() {
	
		$Behavior = new TestAttachableBehavior();
	
		$upload = $this->upload1;
		$config = am($Behavior->defaultSettings, array('allowedFileExtension' => 'jpg'));
		$this->expectException('AttachableUploadException','Invalid file extension');
		$result = $Behavior->_upload($upload, $config);
	}	
		
	
	
	public function testReadSingle() {
		
		$this->_setupDefault();
		
		$data = $this->Attach->read(null, 1);
		$expected = array(
			$this->Attach->alias => array('id' => 1, 'title' => 'Single File', 'file' => 'file1.txt', 'files' => null ),
			'Attachment' => array(
				'file' => array(
					'path' => $this->attachmentDir . $data[$this->Attach->alias]['file'],
					'basename' => $data[$this->Attach->alias]['file'],
					'filename' => 'file1',
					'ext' => 'txt',
				)
			)
		);
		$this->assertEqual($data, $expected);
	}
	
	public function testSaveSingle() {
		$this->_setupDefault();
		$data = array(
				$this->Attach->alias => array('title' => 'New Single File', 'file' => 'file2.txt', 'files' => null ),
		);
		$this->Attach->create();
		$saved = $this->Attach->save($data);
		$this->assertTrue((bool)$saved);
		
		$result = $this->Attach->read(null, $this->Attach->id);
		$expected = array(
				$this->Attach->alias => array('id' => $this->Attach->id, 'title' => 'New Single File', 'file' => 'file2.txt', 'files' => null ),
				'Attachment' => array(
						'file' => array(
								'path' => $this->attachmentDir . $data[$this->Attach->alias]['file'],
								'basename' => $data[$this->Attach->alias]['file'],
								'filename' => 'file2',
								'ext' => 'txt',
						)
				)
		);
		$this->assertEqual($result,$expected);
		
		$this->assertTrue($this->Attach->delete($this->Attach->id,true));
	}
	
	public function testReadMulti() {
		$this->_setupDefault();
		$data = $this->Attach->read(null, 3);
		$expected = array(
				$this->Attach->alias => array('id' => 3, 'title' => 'Multi File', 'file' => null, 'files' => 'file1.txt,file2.txt' ),
				'Attachment' => array(
					'files' => array(
						0 => array(
								'path' => $this->attachmentDir . 'file1.txt',
								'basename' => 'file1.txt',
								'filename' => 'file1',
								'ext' => 'txt',
						),
						1 => array(
								'path' => $this->attachmentDir . 'file2.txt',
								'basename' => 'file2.txt',
								'filename' => 'file2',
								'ext' => 'txt',
						),
					)
				)
		);
		$this->assertEqual($data, $expected);
	}
	
	public function testSingleUploadSaveDelete() {

		$this->_setupDefault();
		$data = array(
			$this->Attach->alias => array(
				'title' => 'My Upload',
				'file' => array(
					'name' => 'Upload File 1.txt',
					'type' => 'text/plain',
					'tmp_name' => $this->tmpDir.'upload1.txt',
					'error' => (int) 0,
					'size' => filesize($this->tmpDir.'upload1.txt')
				)
			)
		);
		
		$this->Attach->create();
		$result = $this->Attach->save($data);
		$expected = array(
			$this->Attach->alias => array(
				'id' => $this->Attach->id,
				'title' => 'My Upload',
				'file' => 'Upload_File_1.txt'
			),
			'Attachment' => array(
				'file' => array(
					'path' => $this->attachmentDir . 'Upload_File_1.txt',
					'basename' => 'Upload_File_1.txt',
					'filename' => 'Upload_File_1',
					'ext' => 'txt',
				)
			),
		);
		$this->assertEqual($result, $expected);
		$this->assertTrue(file_exists($result['Attachment']['file']['path']));
		
		//delete
		$deleted = $this->Attach->delete($this->Attach->id);
		$this->assertTrue($deleted);
		$this->assertTrue(!file_exists($result['Attachment']['file']['path']));
	}
	
	public function testMultiUploadSaveDelete() {

		$this->_setupDefault();
		$data = array(
			$this->Attach->alias => array(
				'title' => 'My Multi Upload',
				'files' => array(
					0 => array(
						'name' => 'Upload File 1.txt',
						'type' => 'text/plain',
						'tmp_name' => $this->tmpDir.'upload1.txt',
						'error' => (int) 0,
						'size' => filesize($this->tmpDir.'upload1.txt')
					),
					1 => array(
						'name' => 'Upload File 2.txt',
						'type' => 'text/plain',
						'tmp_name' => $this->tmpDir.'upload2.txt',
						'error' => (int) 0,
						'size' => filesize($this->tmpDir.'upload2.txt')
					)
				)
			)
		);
		

		$this->Attach->create();
		$result = $this->Attach->save($data);
		$expected = array(
				$this->Attach->alias => array(
					'id' => $this->Attach->id,
					'title' => 'My Multi Upload',
					'files' => 'Upload_File_1.txt,Upload_File_2.txt'
				),
				'Attachment' => array(
					'files' => array(
						0 => array(
							'path' => $this->attachmentDir . 'Upload_File_1.txt',
							'basename' => 'Upload_File_1.txt',
							'filename' => 'Upload_File_1',
							'ext' => 'txt',
						),
						1 => array(
							'path' => $this->attachmentDir . 'Upload_File_2.txt',
							'basename' => 'Upload_File_2.txt',
							'filename' => 'Upload_File_2',
							'ext' => 'txt',
						),
							
					)
				),
		);
		$this->assertEqual($result, $expected);
		$this->assertTrue(file_exists($result['Attachment']['files'][0]['path']));
		$this->assertTrue(file_exists($result['Attachment']['files'][1]['path']));
		

		//delete
		$deleted = $this->Attach->delete($this->Attach->id);
		$this->assertTrue($deleted);
		$this->assertTrue(!file_exists($result['Attachment']['files'][0]['path']));
		$this->assertTrue(!file_exists($result['Attachment']['files'][1]['path']));
	}
	
	public function tearDown() {
		parent::tearDown();
	}
	
}

class TestAttachableBehavior extends AttachableBehavior {
	
	public function _splitBasename($basename) {
		return parent::_splitBasename($basename);
	}
	
	public function _validateMimeType($mime, $allowed) {
		return parent::_validateMimeType($mime, $allowed);
	}
	
	public function _validateFileExtension($ext, $allowed) {
		return parent::_validateFileExtension($ext, $allowed);
	}
	
	public function _validateFileName($filename, $pattern) {
		return parent::_validateFileName($filename, $pattern);
	}
	
	public function _upload($upload, $config) {
		return parent::_upload($upload, $config);
	}
	
}