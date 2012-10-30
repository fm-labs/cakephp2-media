<?php
App::uses('Controller','Controller');
App::uses('Router','Routing');
App::uses('Folder','Utility');
App::uses('UploaderComponent','Media.Controller/Component');

//TODO this is also in AttachmentBehaviorTest. Keep this DRY
define('MEDIA_UPLOAD_TMP_DIR', CakePlugin::path('Media') . 'Test/test_app/tmp/uploadtmp/' );
define('MEDIA_UPLOAD_DIR', CakePlugin::path('Media') . 'Test/test_app/webroot/attachments/' );
define('MEDIA_UPLOAD_TESTFILES_DIR', CakePlugin::path('Media') . 'Test/test_app/tmp/attachments/' );

class UploaderComponentTest extends CakeTestCase {

	public $attachmentDir = MEDIA_UPLOAD_DIR;
	public $tmpDir = MEDIA_UPLOAD_TESTFILES_DIR;
	
	public $fixtures = array('plugin.media.media_upload');

	public $Controller;
	
	public $Uploader;
	
	public $MediaUpload;
	
	public $uploadResult;
	
	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
	
		$request = new CakeRequest(null, false);
	
		$this->Controller = new UploaderTestController($request, $this->getMock('CakeResponse'));
	
		$collection = new ComponentCollection();
		$collection->init($this->Controller);
		
		$this->Uploader = new TestUploaderComponent($collection);
	
		$this->Controller->Components->init($this->Controller);
		$this->Controller->startupProcess();
	
		$this->upload1 = array(
			'name' => 'Upload.jpg',
			'type' => 'image/jpg',
			'tmp_name' => $this->tmpDir.'upload.jpg',
			'error' => (int) 0,
			'size' => filesize($this->tmpDir.'upload.jpg')
		);		

		$this->upload2 = array(
				'name' => 'Upload File 2.txt',
				'type' => 'text/plain',
				'tmp_name' => $this->tmpDir.'upload2.txt',
				'error' => (int) 0,
				'size' => filesize($this->tmpDir.'upload2.txt')
		);
	
		$this->MediaUpload = ClassRegistry::init('MediaUpload');
		$this->Controller->Uploader->setModel($this->MediaUpload);
		
		$this->uploadResult = null;
	}
		
	public function testUpload() {
		
		$uploadData = array('MediaUpload' => array('file_upload'=>$this->upload1));
		
		//upload temporary
		$result = $this->Controller->Uploader->upload($uploadData, array(), true);
		
		$this->assertTrue(isset($result['Attachment']));
		$this->assertTrue(isset($result['Attachment']['file'][0]['tmp_name']));
		$tmpPath1 = $result['Attachment']['file'][0]['tmp_name'];
		$this->assertTrue(file_exists($tmpPath1));
		
		//save with temporary
		$_uploadData = array('MediaUpload' => array('file'=>$result['MediaUpload']['file']));
		$result = $this->MediaUpload->save($_uploadData);
		$this->assertTrue($this->MediaUpload->delete($this->MediaUpload->id));
		
		//upload and create
		$result = $this->Controller->Uploader->upload($uploadData, array(), false);
		
		$this->assertTrue(isset($result['Attachment']));
		$this->assertTrue(isset($result['Attachment']['file'][0]['path']));
		$filePath1 = $result['Attachment']['file'][0]['path'];
		$this->assertTrue(!file_exists($tmpPath1)); //tmpPath1 has moved
		$this->assertTrue(file_exists($filePath1)); //tmpPath1 moved here
		
		//upload and overwrite (non-multi-field)
		$uploadData = array('MediaUpload' => array(
				'id'=>$this->MediaUpload->id, 
				'file'=> $this->upload1['name'], 
				'file_upload'=>$this->upload2
		));
		
		$result = $this->Controller->Uploader->upload($uploadData, array(), false);
		$this->assertTrue(isset($result['Attachment']));
		$this->assertTrue(file_exists($result['Attachment']['file'][0]['path']));
		$filePath2 = $result['Attachment']['file'][0]['path'];
		$this->assertTrue(file_exists($filePath2));
		$this->assertTrue(!file_exists($filePath1));
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

class TestUploaderComponent extends UploaderComponent {
	
	protected function _afterUpload($uploadResult) {
		$this->uploadResult = $uploadResult;
		return parent::_afterUpload($uploadResult);
	}
}

class UploaderTestController extends Controller {
	
	public $name = 'UploaderTest';
	
	public $uses = array('Media.MediaUpload');
	
	public $components = array('Media.Uploader');
	
	/**
	 * construct method
	 *
	 * @return void
	 */
	public function __construct($request, $response) {
		$request->addParams(array('controller'=>'uploader_test','action'=>'upload','plugin'=>'media'));
		$request->here = 'media/uploader_test/upload';
		$request->webroot = '/';
		Router::setRequestInfo($request);
		parent::__construct($request, $response);
	}	

}

?>