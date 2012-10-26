<?php
//define Behavior constants
define('MEDIA_UPLOAD_TMP_DIR', CakePlugin::path('Media') . 'Test/test_app/tmp/uploadtmp/' );
define('MEDIA_UPLOAD_DIR', CakePlugin::path('Media') . 'Test/test_app/webroot/attachments/' );

//define TestCase constants
define('MEDIA_UPLOAD_TESTFILES_DIR', CakePlugin::path('Media') . 'Test/test_app/tmp/attachments/' );

App::uses('AttachableBehavior', 'Media.Model/Behavior');
App::uses('Folder','Utility');

/**
 * 
 * @author flow
 * @todo Test with/without validation options for file input
 */
class AttachableBehaviorTest extends CakeTestCase {
	
	/**
	 * @var array
	 */
	public $fixtures = array('plugin.media.media_upload');
	
	/**
	 * @var MediaUpload
	 */
	public $MediaUpload;
	
	/**
	 * Attachable dir path with trailing DS
	 * @var string
	 */
	
	public $attachmentDir = MEDIA_UPLOAD_DIR;
	public $tmpDir = MEDIA_UPLOAD_TESTFILES_DIR;
	
	protected $upload1;
	protected $upload2;
	protected $uploadNoExt;
	protected $uploadImage;
	
	
	public function setUp() {
		parent::setUp();

		if (!is_dir(MEDIA_UPLOAD_TMP_DIR) || !is_writeable(MEDIA_UPLOAD_TMP_DIR)) {
			throw new Exception('Test Tmp dir not found or not writeable');
		}
		
		if (!is_dir(MEDIA_UPLOAD_DIR) || !is_writeable(MEDIA_UPLOAD_DIR)) {
			throw new Exception('Test upload dir not found or not writeable');
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
		
		$this->uploadNoExt = array(
				'name' => 'Upload_Without_Ext',
				'type' => 'text/plain',
				'tmp_name' => $this->tmpDir.'upload_noext',
				'error' => (int) 0,
				'size' => filesize($this->tmpDir.'upload_noext')
		);

		$this->uploadImage = array(
				'name' => 'Upload.jpg',
				'type' => 'image/jpg',
				'tmp_name' => $this->tmpDir.'upload.jpg',
				'error' => (int) 0,
				'size' => filesize($this->tmpDir.'upload.jpg')
		);
		
		$this->MediaUpload = ClassRegistry::init('Media.MediaUpload');
		
	}
	
	protected function _getExpectedAttachment($upload, $expectedName) {
		
		list($filename, $ext, $dotExt) = TestAttachableBehavior::splitBasename($expectedName);
		
		return array(
				'path' => $this->attachmentDir . $expectedName,
				'basename' => $expectedName,
				'filename' => $filename,
				'ext' => $ext,
                //'size' => $upload['size'],
                //'type' => $upload['type']
		);
	}
	
	protected function _setupDefault() {
		
		$this->MediaUpload->configureAttachment(array(
			'file' => array(
					'baseDir' => $this->attachmentDir,
					'multiple' => false,
			),
			'files' => array(
					'baseDir' => $this->attachmentDir,
					'multiple' => true,
			)
		),true);
		$this->MediaUpload->validate = array();
		$this->MediaUpload->Behaviors->load('Media.Attachable',array());
	}

	protected function _setupImages() {
	
		$this->MediaUpload->configureAttachment(array(
				'file' => array(
						'baseDir' => $this->attachmentDir,
						'multiple' => false,
						'preview' => true
				),
				'files' => array(
						'baseDir' => $this->attachmentDir,
						'multiple' => true,
						'preview' => array(
							'small' => array('width' => 50, 'height' => 50),
							'big' => array('width' => 50, 'height' => 50),
						)
				)
		),true);
		$this->MediaUpload->validate = array();
		$this->MediaUpload->Behaviors->load('Media.Attachable',array());
	}
	
	public function testStaticCacheKeyMethods() {
		
		//getCacheKeyStringPattern()
		$result = TestAttachableBehavior::getCacheKeyStringPattern();
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

		$this->MediaUpload->attachments = array(
				'file' => array()
		);
		$Behavior->setup($this->MediaUpload);
		$result = $Behavior->getModelSettings($this->MediaUpload);
		
		$expected = array(
			'file' => array(
				'uploadField' => 'file_upload',
				'baseDir' => MEDIA_UPLOAD_DIR,
				'multiple' => false,
				'preserve' => true,
				'minFileSize' => (int) 0,
				'maxFileSize' => 2 * 1024 * 1024,
				'allowEmpty' => true,
				'allowOverwrite' => false,
				'allowedMimeType' => '*',
				'allowedFileExtension' => '*',
				'hashFilename' => false,
				'slug' => '_',
				'removeOnDelete' => true,
				'preview' => false,
				'thumbDir' => MEDIA_THUMB_CACHE_DIR
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


	public function testValidateWithNoFileSubmitted() {

		$this->_setupDefault();
		
		$this->MediaUpload->validate = array(
				'file_upload' => array(
						'notempty' => array(
								'rule' => array('notempty'),
								'message' => 'FileUpload can not be empty',
								'required' => true,
						),
				)
		);
		
		//no file has been submitted
		$data = array(
				'MediaUpload' => array(
						'title' => 'Upload',
						'file' => '',
						'file_upload' => array(
								'name' => '',
								'type' => '',
								'tmp_name' => '',
								'error' => (int) 4,
								'size' => (int) 0
						)
				)
		);
		$expectedResultData = array(
			'MediaUpload' => array(
				'title' => 'Upload',
				'file' => '',
				//'file_upload' => null
			)
		);
		
		$this->MediaUpload->create($data);
		$result = $this->MediaUpload->validates();
		$resultData = $this->MediaUpload->data;
		$resultValidationErrors = $this->MediaUpload->validationErrors;
		
		$this->assertTrue(($result === false));
		$this->assertEqual($resultData, $expectedResultData);
		$this->assertTrue(isset($resultValidationErrors['file_upload']));
	}
	
	/**
	 * TODO clean this up. split up in separate test methods
	 */
	public function testAttachTemporary() {
	
		$this->_setupDefault();
		
		//test data with no file submitted
		$data = array(
				'MediaUpload' => array(
						'title' => 'Upload',
						'file' => '',
						'file_upload' => array(
								'name' => '',
								'type' => '',
								'tmp_name' => '',
								'error' => (int) 4,
								'size' => (int) 0
						)
				)
		);
		$expectedResultData = array(
			'MediaUpload' => array(
				'title' => 'Upload',
				'file' => '',
				//'file_upload' => null
			)
		);
		$this->MediaUpload->create($data);
		$result = $this->MediaUpload->attachTemporary();
		$resultData = $this->MediaUpload->data;
		$this->assertTrue(($result === false));
		$this->assertEqual($resultData, $expectedResultData);
		
		$data = array(
				'MediaUpload' => array(
						'file_upload' => array(
								'name' => 'Upload File 1.txt',
								'type' => 'text/plain',
								'tmp_name' => $this->tmpDir.'upload1.txt',
								'error' => (int) 0,
								'size' => filesize($this->tmpDir.'upload1.txt')
						)
				)
		);
	
		//set data using Model::set($data) and use a custom cache key here
		$customCacheKey = 'my-cache-key';
		$this->MediaUpload->create();
		$this->MediaUpload->set($data);
		$result = $this->MediaUpload->attachTemporary(null, $customCacheKey);
		$resultData = $this->MediaUpload->data;
	
		$expectedKeyString = '@my-cache-key@';
	
		//test manual generation
		$resultKeyString = String::insert(TestAttachableBehavior::CACHE_KEY_INSERTSTRING,
				array('cacheKey'=>$customCacheKey));
		$this->assertEqual($resultKeyString, $expectedKeyString);
	
		//test implemented generation
		$resultKeyString = TestAttachableBehavior::getCacheKeyString($customCacheKey);
		$this->assertEqual($resultKeyString, $expectedKeyString);
	
		//test if set
		$this->assertTrue($result);
		$this->assertEqual($resultData['MediaUpload']['file'], $expectedKeyString);
		$this->assertEqual($resultData['MediaUpload']['file_upload'], $expectedKeyString);
	
		//now try to save again in new request and cache key passed
	}	
	
	
	public function testReadSingle() {
		
		$this->_setupDefault();
		
		$expectedBare = array(
			'MediaUpload' => array('id' => 1, 'title' => 'Single File', 'file' => 'file1.txt', 'files' => null ),
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
		$data = $this->MediaUpload->read(null, 1);
		$this->assertEqual($data, $expected);
	
		//the "find way"
		$data = $this->MediaUpload->find('first',array('attachment'=>false));
		$this->assertEqual($data, $expectedBare);

		//second find without ->attachment() should contain attachment
		$data = $this->MediaUpload->find('first');
		$this->assertEqual($data, $expected);
		
		//the "find way" for a field
		$data = $this->MediaUpload->find('first',array('attachment'=>array('file'=>false)));
		$this->assertEqual($data, $expectedBare);
		
		//the "read" way
		$this->MediaUpload->attachment(false);
		$data = $this->MediaUpload->read(null, 1);
		$this->assertEqual($data, $expectedBare);
		
		//second read without ->attachment() should contain attachment
		$data = $this->MediaUpload->read(null, 1);
		$this->assertEqual($data, $expected);
		
		//the "read" way for a single field
		$this->MediaUpload->attachment(false);
		$data = $this->MediaUpload->read(null, 1);
		$this->assertEqual($data, $expectedBare);
	}	
	
	public function testCreateSingleWithFileName() {
		$this->_setupDefault();
		$data = array(
				'MediaUpload' => array('title' => 'New Single File', 'file' => 'file2.txt', 'files' => null ),
		);
		$this->MediaUpload->create();
		$saved = $this->MediaUpload->save($data);
		$this->assertTrue((bool)$saved);
		
		$result = $this->MediaUpload->read(null, $this->MediaUpload->id);
		$expected = array(
				'MediaUpload' => array('id' => $this->MediaUpload->id, 'title' => 'New Single File', 'file' => 'file2.txt', 'files' => null ),
				'Attachment' => array(
						'file' => array(
							0 => array(
								'path' => $this->attachmentDir . $data['MediaUpload']['file'],
								'basename' => $data['MediaUpload']['file'],
								'filename' => 'file2',
								'ext' => 'txt',
							)
						)
				)
		);
		$this->assertEqual($result,$expected);
		
		$this->assertTrue($this->MediaUpload->delete($this->MediaUpload->id,true));
	}
	
	public function testReadMulti() {
		$this->_setupDefault();
		$data = $this->MediaUpload->read(null, 3);
		$expected = array(
				'MediaUpload' => array('id' => 3, 'title' => 'Multi File', 'file' => null, 'files' => 'file1.txt,file2.txt' ),
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
			'MediaUpload' => array(
				'title' => 'My Upload',
				'file_upload' => $this->upload1
			)
		);
		
		$this->MediaUpload->create();
		$result = $this->MediaUpload->save($data);
		$expected = array(
			'MediaUpload' => array(
				'id' => $this->MediaUpload->id,
				'title' => 'My Upload',
				'file' => 'Upload_File_1.txt'
			),
			'Attachment' => array(
				'file' => array(
					0 => $this->_getExpectedAttachment($this->upload1, 'Upload_File_1.txt')
				)
			),
		);
		$this->assertEqual($result, $expected);
		$this->assertTrue(file_exists($result['Attachment']['file'][0]['path']));
		
		//delete
		$deleted = $this->MediaUpload->delete($this->MediaUpload->id);
		$this->assertTrue($deleted);
		$this->assertTrue(!file_exists($result['Attachment']['file'][0]['path']));
	}

	public function testSingleUploadSaveWithOtherValidationError() {
	
		$this->_setupDefault();
		$this->MediaUpload->validator()->add('title', 'notempty',array('rule'=>'notEmpty'));
		
		//try to submit data where an other field won't pass validation
		$data = array(
				'MediaUpload' => array(
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
	
		$this->MediaUpload->create();
		$result = $this->MediaUpload->save($data);
		$modelData = $this->MediaUpload->data;
		
		$this->assertEqual($result, false);
		$this->assertTrue(isset($this->MediaUpload->validationErrors['title']));
		$this->assertTrue(isset($modelData['MediaUpload']['file']));
		
		$result = TestAttachableBehavior::getCacheKey($modelData['MediaUpload']['file']);
		$this->assertNotEqual($result, false);
		
		//resolve errors and submit again
		//the temporary upload should continue
		
		$data['MediaUpload']['title'] = 'Has title now';
		$this->MediaUpload->create();
		$result = $this->MediaUpload->save($data);
		$expected = array(
			'MediaUpload' => array(
				'id' => $this->MediaUpload->id,
				'title' => 'Has title now',
				'file' => 'Upload_File_1.txt'
			),
			'Attachment' => array(
				'file' => array(
					0 => $this->_getExpectedAttachment($this->upload1, 'Upload_File_1.txt')
				)
			),
		);
		$this->assertEqual($result, $expected);
		
		//delete
		$deleted = $this->MediaUpload->delete($this->MediaUpload->id);
		$this->assertTrue($deleted);
		$this->assertTrue(!file_exists($result['Attachment']['file'][0]['path']));
	}	
	
	public function testSingleEditWithUpload() {
		
		$this->_setupDefault();
		// ### szenario 1: row has no file set. we upload one. ###
		
		//get row with no file
		$result = $this->MediaUpload->read(null, 2); //the second record in MediaUploadFixture has no file
		$this->assertTrue(empty($result['MediaUpload']['file']));
		
		//add upload
		$data = $result;
		$data['MediaUpload']['file_upload'] = array(
				0 => $this->upload1
		);
		$expected = $result;
		$expected['MediaUpload']['file'] = 'Upload_File_1.txt';
		$expected['Attachment']['file'][0] = $this->_getExpectedAttachment($this->upload1, 'Upload_File_1.txt');
		
		$this->MediaUpload->create();
		$result = $this->MediaUpload->save($data);
		$this->assertEqual($result, $expected);
		
		$expectedPath1 = $expected['Attachment']['file'][0]['path'];
		$this->assertTrue(file_exists($expectedPath1));
		
		// ### szenerio 2: record has attachment. upload new file and replace file.
		
		$data = $result;
		$data['MediaUpload']['file_upload'] = array(
				0 => $this->upload2
		);
		$this->MediaUpload->create();
		$result = $this->MediaUpload->save($data);
		
		$expected = $result;
		$expected['MediaUpload']['file'] = 'Upload_File_2.txt';
		$expected['Attachment']['file'][0] = $this->_getExpectedAttachment($this->upload2, 'Upload_File_2.txt');
		$result = $this->MediaUpload->read(null, 2);
		$this->assertEqual($result, $expected);
		
		$expectedPath2 = $expected['Attachment']['file'][0]['path'];
		$this->assertTrue(file_exists($expectedPath2));
		$this->assertTrue(!file_exists($expectedPath1));
		
		
		// ### szenario 3: repeat szenario 1 & 2 with multiple
		$this->MediaUpload->attachment(false);
		$record = $this->MediaUpload->read(null, 2);
		$this->assertTrue(empty($record['MediaUpload']['files']));
		
		$data = $record;
		$data['MediaUpload']['files_upload'] = array(
				0 => $this->upload2, //<-- this file has been uploaded before in szenario 2!
				1 => $this->upload2 //<-- uploading the same file twice! tricky, ha..
		);
		$expected = $record;
		$expected['MediaUpload']['files'] = 'Upload_File_2_1.txt,Upload_File_2_2.txt';
		$expected['Attachment']['files'][0] = $this->_getExpectedAttachment($this->upload2, 'Upload_File_2_1.txt');
		$expected['Attachment']['files'][1] = $this->_getExpectedAttachment($this->upload2, 'Upload_File_2_2.txt');
		$this->MediaUpload->create();
		$result = $this->MediaUpload->save($data);
		$this->assertEqual($result, $expected);
		
		$expectedPathFiles1 = $expected['Attachment']['files'][0]['path'];
		$this->assertTrue(file_exists($expectedPathFiles1));
		
		$expectedPathFiles2 = $expected['Attachment']['files'][1]['path'];
		$this->assertTrue(file_exists($expectedPathFiles2));
		
		// ### szenario 4. We upload some more files. Default behavior is preserving the attachments.
		$data = $result;
		$data['MediaUpload']['files_upload'] = array(
				0 => $this->upload1,
				1 => $this->upload1
		);

		$expected['MediaUpload']['files'] = 'Upload_File_2_1.txt,Upload_File_2_2.txt,Upload_File_1.txt,Upload_File_1_1.txt';
		$expected['Attachment']['files'][0] = $this->_getExpectedAttachment($this->upload2, 'Upload_File_2_1.txt');
		$expected['Attachment']['files'][1] = $this->_getExpectedAttachment($this->upload2, 'Upload_File_2_2.txt');
		
		// the file uploaded in szenario 1 has already been overwritten by szenario 2, so 'Upload_File_1.txt' is available again
		$expected['Attachment']['files'][2] = $this->_getExpectedAttachment($this->upload1, 'Upload_File_1.txt');
		$expected['Attachment']['files'][3] = $this->_getExpectedAttachment($this->upload1, 'Upload_File_1_1.txt');

		$this->MediaUpload->create();
		$result = $this->MediaUpload->save($data);
		$this->assertEqual($result, $expected);
		
		$expectedPathFiles1 = $expected['Attachment']['files'][0]['path'];
		$this->assertTrue(file_exists($expectedPathFiles1));
		
		$expectedPathFiles2 = $expected['Attachment']['files'][1]['path'];
		$this->assertTrue(file_exists($expectedPathFiles2));
		
		$expectedPathFiles3 = $this->attachmentDir . 'Upload_File_1.txt';
		$this->assertTrue(file_exists($expectedPathFiles3));
		
		$expectedPathFiles4 = $this->attachmentDir . 'Upload_File_1_1.txt';
		$this->assertTrue(file_exists($expectedPathFiles4));
		
		// ### szenario 5: repeat szenario 4, but do not preserve attachments$data = $result;

		$data = $record;
		$data['MediaUpload']['files_upload'] = array(
				0 => $this->upload1,
				1 => $this->upload2
		);
		$data['MediaUpload']['files'] = $result['MediaUpload']['files'];

		$expected = $record;
		$expected['MediaUpload']['files'] = 'Upload_File_1_2.txt,Upload_File_2_3.txt';
		$expected['Attachment']['files'][0] = $this->_getExpectedAttachment($this->upload1, 'Upload_File_1_2.txt');
		$expected['Attachment']['files'][1] = $this->_getExpectedAttachment($this->upload2, 'Upload_File_2_3.txt');

		$this->MediaUpload->create();
		$result = $this->MediaUpload->save($data,array('attachment'=>array('preserve'=>false)));
		$this->assertEqual($result, $expected);		

		$expectedPathFiles5 = $expected['Attachment']['files'][0]['path'];
		$this->assertTrue(file_exists($expectedPathFiles5));
		
		$expectedPathFiles6 = $expected['Attachment']['files'][1]['path'];
		$this->assertTrue(file_exists($expectedPathFiles6));
		
		$this->assertTrue(!file_exists($expectedPathFiles1));
		$this->assertTrue(!file_exists($expectedPathFiles2));
		$this->assertTrue(!file_exists($expectedPathFiles3));
		$this->assertTrue(!file_exists($expectedPathFiles4));
				
		// ### finally check if we got it right alltogether
		$record = $this->MediaUpload->read(null, 2);
		$this->assertEqual($record['MediaUpload']['file'], 'Upload_File_2.txt');
		$this->assertEqual($record['MediaUpload']['files'], 'Upload_File_1_2.txt,Upload_File_2_3.txt');

		$this->assertTrue(isset($record['Attachment']['file'][0]));
		$this->assertTrue(!isset($record['Attachment']['file'][1]));
		$this->assertTrue(isset($record['Attachment']['files'][0]));
		$this->assertTrue(isset($record['Attachment']['files'][1]));
		$this->assertTrue(!isset($record['Attachment']['files'][2]));
		
		$this->assertTrue(!file_exists($expectedPath1));
		$this->assertTrue(file_exists($expectedPath2));
		$this->assertTrue(!file_exists($expectedPathFiles1));
		$this->assertTrue(!file_exists($expectedPathFiles2));
		$this->assertTrue(!file_exists($expectedPathFiles3));
		$this->assertTrue(!file_exists($expectedPathFiles4));
		$this->assertTrue(file_exists($expectedPathFiles5));
		$this->assertTrue(file_exists($expectedPathFiles6));
		
	}
	
	public function testMultiUploadSaveDelete() {

		$this->_setupDefault();
		$data = array(
			'MediaUpload' => array(
				'title' => 'My Multi Upload',
				'files_upload' => array(
					0 => $this->upload1,
					1 => $this->upload2
				)
			)
		);
		

		$this->MediaUpload->create();
		$result = $this->MediaUpload->save($data);
		$expected = array(
				'MediaUpload' => array(
					'id' => $this->MediaUpload->id,
					'title' => 'My Multi Upload',
					'files' => 'Upload_File_1.txt,Upload_File_2.txt'
				),
				'Attachment' => array(
					'files' => array(
						0 => $this->_getExpectedAttachment($this->upload1, 'Upload_File_1.txt'),
						1 => $this->_getExpectedAttachment($this->upload2, 'Upload_File_2.txt'),
							
					)
				),
		);
		$this->assertEqual($result, $expected);
		$this->assertTrue(file_exists($result['Attachment']['files'][0]['path']));
		$this->assertTrue(file_exists($result['Attachment']['files'][1]['path']));
		

		//delete
		$deleted = $this->MediaUpload->delete($this->MediaUpload->id);
		$this->assertTrue($deleted);
		$this->assertTrue(!file_exists($result['Attachment']['files'][0]['path']));
		$this->assertTrue(!file_exists($result['Attachment']['files'][1]['path']));
	}
	
	
	public function testCombinedSingleMultiUploadSaveDelete() {
	
		$this->_setupDefault();
				$data = array(
				'MediaUpload' => array(
						'title' => 'My Multi Upload',
						'file_upload' => $this->uploadNoExt,
						'files_upload' => array(
								0 => $this->upload1,
								1 => $this->upload2
						)
				)
		);
	
	
		$this->MediaUpload->create();
		$result = $this->MediaUpload->save($data);
		$expected = array(
				'MediaUpload' => array(
						'id' => $this->MediaUpload->id,
						'title' => 'My Multi Upload',
						'file' => 'Upload_Without_Ext',
						'files' => 'Upload_File_1.txt,Upload_File_2.txt'
				),
				'Attachment' => array(
						'file' => array(
							0 => $this->_getExpectedAttachment($this->uploadNoExt, 'Upload_Without_Ext')
						),
						'files' => array(
							0 => $this->_getExpectedAttachment($this->upload1, 'Upload_File_1.txt'),
							1 => $this->_getExpectedAttachment($this->upload2, 'Upload_File_2.txt'),
						)
				),
		);
		$this->assertEqual($result, $expected);
		$this->assertTrue(file_exists($result['Attachment']['file'][0]['path']));
		$this->assertTrue(file_exists($result['Attachment']['files'][0]['path']));
		$this->assertTrue(file_exists($result['Attachment']['files'][1]['path']));
	
	
		//delete
		$deleted = $this->MediaUpload->delete($this->MediaUpload->id);
		$this->assertTrue($deleted);
		$this->assertTrue(!file_exists($result['Attachment']['file'][0]['path']));
		$this->assertTrue(!file_exists($result['Attachment']['files'][0]['path']));
		$this->assertTrue(!file_exists($result['Attachment']['files'][1]['path']));
	}	


	public function testSaveWithPreview() {
		$this->_setupImages();
	
		$data = array('MediaUpload' => array('file_upload'=>$this->uploadImage, 'files_upload'=>$this->uploadImage));
		$this->MediaUpload->create();
		$result = $this->MediaUpload->save($data);
	
		$this->assertTrue(isset($result['Attachment']['file'][0]['preview']['default']));
		$this->assertTrue(file_exists($result['Attachment']['file'][0]['preview']['default']));
		$this->assertTrue(isset($result['Attachment']['files'][0]['preview']['small']));
		$this->assertTrue(file_exists($result['Attachment']['files'][0]['preview']['small']));
		$this->assertTrue(isset($result['Attachment']['files'][0]['preview']['big']));
		$this->assertTrue(file_exists($result['Attachment']['files'][0]['preview']['big']));
	
	}	
	
	public function tearDown() {
		parent::tearDown();
		
		//cleanup attachmentDir
		$Folder = new Folder($this->attachmentDir);
		list($dir, $files) = $Folder->read(true,array('empty'),true);
		foreach($files as $file) {
			@unlink($file);
		}		
	}
	
}

class TestAttachableBehavior extends AttachableBehavior {
	
	
	public function _createPreview($attachment, $config) {
		return parent::_createPreview($attachment, $config);
	}
	
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