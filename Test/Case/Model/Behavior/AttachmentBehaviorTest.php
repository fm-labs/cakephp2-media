<?php
define('MEDIA_UPLOAD_TMP_DIR', CakePlugin::path('Media') . 'Test/test_app/tmp/attachments/' );
define('MEDIA_UPLOAD_DIR', CakePlugin::path('Media') . 'Test/test_app/webroot/attachments/' );

App::uses('AttachableBehavior', 'Media.Model/Behavior');
App::uses('Folder','Utility');

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
	
	public $attachmentDir = MEDIA_UPLOAD_DIR;
	public $tmpDir = MEDIA_UPLOAD_TMP_DIR;
	
	protected $file1;
	protected $file2;
	
	public function setUp() {
		parent::setUp();
		
		//cleanup attachmentDir
		$Folder = new Folder($this->attachmentDir);
		list($dir, $files) = $Folder->read(true,array('empty'),true); 
		foreach($files as $file) {
			@unlink($file);
		}
		
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
	
	public function testStaticCacheKeyMethods() {
		
		//getCacheKeyPattern()
		$result = TestAttachableBehavior::getCacheKeyPattern();
		$expected = '/^@(.*)@$/';
		$this->assertEqual($result, $expected);
		
		//getCacheKeyString()
		$cacheKey = '12345-XYZ0-1234';
		$result = TestAttachableBehavior::getCacheKeyString($cacheKey);
		$expected = '@'.$cacheKey.'@';
		$this->assertEqual($result, $expected);
		
		//getCacheKey()
		$result = TestAttachableBehavior::getCacheKey($result);
		$expected = $cacheKey;
		$this->assertEqual($result, $expected);
	}

	public function testDefaultConfig() {
		$Behavior = new TestAttachableBehavior();

		$this->Attach->attachments = array(
				'file' => array()
		);
		$Behavior->setup($this->Attach);
		$result = $Behavior->getModelSettings($this->Attach);
		
		$expected = array(
			'file' => array(
				'uploadField' => 'file_upload',
				'dir' => MEDIA_UPLOAD_DIR,
				'multiple' => false,
				'minFileSize' => (int) 0,
				'maxFileSize' => 2 * 1024 * 1024,
				'allowEmpty' => true,
				'allowOverwrite' => false,
				'allowedMimeType' => '*',
				'allowedFileExtension' => '*',
				'hashFilename' => false,
				'slug' => '_',
				'removeOnDelete' => true
			)
		);
		$this->assertEqual($result, $expected);
	}
	
	public function testSplitBasename() {
		$Behavior = new TestAttachableBehavior();
		
		$result = $Behavior->_splitBasename('file.txt');
		$this->assertEqual($result, array('file','txt','.txt'));
		
		$result = $Behavior->_splitBasename('.htaccess');
		$this->assertEqual($result, array('','htaccess','.htaccess'));

		$result = $Behavior->_splitBasename('file');
		$this->assertEqual($result, array('file',null,null));
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
		$result = $Behavior->_upload($upload, $Behavior->defaultConfig);
	}
	
	public function testUploadWithPartialUploadError() {

		$Behavior = new TestAttachableBehavior();
		
		$upload = array(
			'error' => UPLOAD_ERR_PARTIAL,
		);
		$this->expectException('AttachableUploadException','File only partially uploaded');
		$result = $Behavior->_upload($upload, $Behavior->defaultConfig);
	}

	public function testUploadWithMinFileSizeError() {
	
		$Behavior = new TestAttachableBehavior();
	
		$upload = am($this->upload1,array( 'size' => 0 ));
		$config = am($Behavior->defaultConfig, array('minFileSize' => 1));
		$this->expectException('AttachableUploadException','Minimum file size error');
		$result = $Behavior->_upload($upload, $config);
	}

	public function testUploadWithMaxFileSizeError() {
	
		$Behavior = new TestAttachableBehavior();
	
		$upload = am($this->upload1,array( 'size' => 2 ));
		$config = am($Behavior->defaultConfig, array('maxFileSize' => 1));
		$this->expectException('AttachableUploadException','Maximum file size exceeded');
		$result = $Behavior->_upload($upload, $config);
	}	

	public function testUploadWithMimeTypeError() {
	
		$Behavior = new TestAttachableBehavior();

		$upload = am($this->upload1,array( 'type' => 'text/plain' ));
		$config = am($Behavior->defaultConfig, array('allowedMimeType' => 'image/jpg'));
		$this->expectException('AttachableUploadException','Invalid mime type');
		$result = $Behavior->_upload($upload, $config);
	}

	public function testUploadWithFileExtensionError() {
	
		$Behavior = new TestAttachableBehavior();
	
		$upload = $this->upload1;
		$config = am($Behavior->defaultConfig, array('allowedFileExtension' => 'jpg'));
		$this->expectException('AttachableUploadException','Invalid file extension');
		$result = $Behavior->_upload($upload, $config);
	}	
		
	
	
	public function testReadSingle() {
		
		$this->_setupDefault();
		
		$expectedBare = array(
			$this->Attach->alias => array('id' => 1, 'title' => 'Single File', 'file' => 'file1.txt', 'files' => null ),
		);
		$expected = am($expectedBare,array(
			'Attachment' => array(
				'file' => array(
					0 => array(
						'path' => $this->attachmentDir . 'file1.txt',
						'basename' => 'file1.txt',
						'filename' => 'file1',
						'ext' => 'txt',
					)
				)
			))
		);
		$data = $this->Attach->read(null, 1);
		$this->assertEqual($data, $expected);
	
		//the "find way"
		$data = $this->Attach->find('first',array('attachment'=>false));
		$this->assertEqual($data, $expectedBare);

		//second find without ->attachment() should contain attachment
		$data = $this->Attach->find('first');
		$this->assertEqual($data, $expected);
		
		//the "find way" for a field
		$data = $this->Attach->find('first',array('attachment'=>array('file'=>false)));
		$this->assertEqual($data, $expectedBare);
		
		//the "read" way
		$this->Attach->attachment(false);
		$data = $this->Attach->read(null, 1);
		$this->assertEqual($data, $expectedBare);
		
		//second read without ->attachment() should contain attachment
		$data = $this->Attach->read(null, 1);
		$this->assertEqual($data, $expected);
		
		//the "read" way for a single field
		$this->Attach->attachment(false);
		$data = $this->Attach->read(null, 1);
		$this->assertEqual($data, $expectedBare);
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
							0 => array(
								'path' => $this->attachmentDir . $data[$this->Attach->alias]['file'],
								'basename' => $data[$this->Attach->alias]['file'],
								'filename' => 'file2',
								'ext' => 'txt',
							)
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
				'file_upload' => array(
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
					0 => array(
						'path' => $this->attachmentDir . 'Upload_File_1.txt',
						'basename' => 'Upload_File_1.txt',
						'filename' => 'Upload_File_1',
						'ext' => 'txt',
					)
				)
			),
		);
		$this->assertEqual($result, $expected);
		$this->assertTrue(file_exists($result['Attachment']['file'][0]['path']));
		
		//delete
		$deleted = $this->Attach->delete($this->Attach->id);
		$this->assertTrue($deleted);
		$this->assertTrue(!file_exists($result['Attachment']['file'][0]['path']));
	}


	public function testSingleUploadSaveWithOtherValidationError() {
	
		$this->_setupDefault();
		$this->Attach->validator()->add('title', 'notempty',array('rule'=>'notEmpty'));
		
		//try to submit data where an other field won't pass validation
		$data = array(
				$this->Attach->alias => array(
						'title' => '',
						'file_upload' => array(
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
		$this->assertEqual($result, false);
		$this->assertTrue(isset($this->Attach->validationErrors['title']));
		
		$modelData = $this->Attach->data;
		$this->assertTrue(isset($modelData[$this->Attach->alias]['file']));
		$result = TestAttachableBehavior::getCacheKey($modelData[$this->Attach->alias]['file']);
		$this->assertNotEqual($result, false);
		
		//resolve errors and submit again
		//the temporary upload should continue
		
		$data[$this->Attach->alias]['title'] = 'Has title now';
		$this->Attach->create();
		$result = $this->Attach->save($data);
		$expected = array(
			$this->Attach->alias => array(
				'id' => $this->Attach->id,
				'title' => 'Has title now',
				'file' => 'Upload_File_1.txt'
			),
			'Attachment' => array(
				'file' => array(
					0 => array(
						'path' => $this->attachmentDir . 'Upload_File_1.txt',
						'basename' => 'Upload_File_1.txt',
						'filename' => 'Upload_File_1',
						'ext' => 'txt',
					)
				)
			),
		);
		$this->assertEqual($result, $expected);
		
		//delete
		$deleted = $this->Attach->delete($this->Attach->id);
		$this->assertTrue($deleted);
		$this->assertTrue(!file_exists($result['Attachment']['file'][0]['path']));
	}	
	
	public function testMultiUploadSaveDelete() {

		$this->_setupDefault();
		$data = array(
			$this->Attach->alias => array(
				'title' => 'My Multi Upload',
				'files_upload' => array(
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
	
	public function testCombinedSingleMultiUploadSaveDelete() {
	
		$this->_setupDefault();
		$data = array(
				$this->Attach->alias => array(
						'title' => 'My Multi Upload',
						'file_upload' => array(
								'name' => 'Upload_Without_Ext',
								'type' => 'text/plain',
								'tmp_name' => $this->tmpDir.'upload_noext',
								'error' => (int) 0,
								'size' => filesize($this->tmpDir.'upload_noext')
						),
						'files_upload' => array(
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
						'file' => 'Upload_Without_Ext',
						'files' => 'Upload_File_1.txt,Upload_File_2.txt'
				),
				'Attachment' => array(
						'file' => array(
							0 => array(
								'path' => $this->attachmentDir . 'Upload_Without_Ext',
								'basename' => 'Upload_Without_Ext',
								'filename' => 'Upload_Without_Ext',
								'ext' => '',
							)
						),
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
		$this->assertTrue(file_exists($result['Attachment']['file'][0]['path']));
		$this->assertTrue(file_exists($result['Attachment']['files'][0]['path']));
		$this->assertTrue(file_exists($result['Attachment']['files'][1]['path']));
	
	
		//delete
		$deleted = $this->Attach->delete($this->Attach->id);
		$this->assertTrue($deleted);
		$this->assertTrue(!file_exists($result['Attachment']['file'][0]['path']));
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
	
	public function getModelSettings(Model $model) {
		return $this->settings[$model->alias];
	}
	
}