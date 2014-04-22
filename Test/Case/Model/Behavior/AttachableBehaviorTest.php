<?php
require_once (dirname(dirname(__DIR__)) . DS . 'MediaPluginTestCase.php');

App::uses('AttachableBehavior', 'Media.Model/Behavior');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

/**
 * 
 * @author flow
 * @todo Test with/without validation options for file input
 */
class AttachableBehaviorTest extends MediaPluginTestCase {

/**
 * @var array
 */
	public $fixtures = array('plugin.media.attachable_model');
	
/**
 * @var Model
*/
	public $Model;
	
/**
 * Directory with dummy upload files
 *
 * @var string
 */
	public $dummyDir = MEDIA_TESTAPP_DUMMYDIR;
		
/**
 * Attachment storage path
 *
 * @var string
 */
	public $attachmentDir = MEDIA_TESTAPP_UPLOADDIR;
	
/**
 * Dummy upload data
 *
 * @var array
 */
	protected $upload1;
	protected $upload2;
	protected $uploadNoExt;
	protected $uploadImage;
	protected $uploadEmpty;

	public function setUp() {
		parent::setUp();

		// setup test upload dir
		$this->UploadFolder = new Folder($this->attachmentDir, true, 0777);

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

		$this->Model = ClassRegistry::init('Media.AttachableModel');
	}

	protected function _setupDefault() {
		$this->Model->Behaviors->load('Media.Attachable', array());
		$this->Model->configureAttachment(array(
				'file' => array(
						'baseDir' => $this->attachmentDir,
						'multiple' => false,
						'removeOnOverwrite' => true,
				),
				'files' => array(
						'baseDir' => $this->attachmentDir,
						'multiple' => true,
						'removeOnOverwrite' => true,
				)
		), true);
		//$this->Model->validate = array();
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
		$this->assertEqual($this->UploadFolder->pwd(), $this->attachmentDir);
	}

	public function testDefaultConfig() {
		$Behavior = new TestAttachableBehavior();

		$this->Model->attachments = array(
			'file' => array(
				'baseDir' => $this->attachmentDir
			)
		);
		$Behavior->setup($this->Model);
		$result = $Behavior->getSettings($this->Model);

		$expected = array(
			'file' => array(
				'enabled' => true,
				'uploadField' => 'file_upload',
				#'uploadNameField' => 'file_name',
				'baseDir' => $this->attachmentDir,
				'baseUrl' => false,
				'subDir' => '',
				'multiple' => false,
				#'append' => true,
				'removeOnDelete' => true,
				'removeOnOverwrite' => true,
				'allowEmpty' => true,
				'minFilesize' => (int)0,
				'maxFilesize' => 2 * 1024 * 1024,
				'allowOverwrite' => false,
				'allowedMimeType' => '*',
				'allowedFileExtension' => '*',
				'hashFilename' => false,
				'uniqueFilename' => true,
				'slug' => '_',
				'filename' => null
			)
		);
		$this->assertEqual($result, $expected);
	}

	public function testReplacePathTokens() {
		$this->Model->id = 999;

		$result = TestAttachableBehavior::replacePathTokens($this->Model, 'test{DS}');
		$expected = 'test' . DS;
		$this->assertEqual($result, $expected);

		$result = TestAttachableBehavior::replacePathTokens($this->Model, '{MODEL}{DS}');
		$expected = 'attachable_model' . DS;
		$this->assertEqual($result, $expected);

		$result = TestAttachableBehavior::replacePathTokens($this->Model, '{MODEL}{DS}{MODELID}{DS}');
		$expected = 'attachable_model' . DS . '999' . DS;
		$this->assertEqual($result, $expected);
	}

	public function testSingleUploadSaveReadEditDelete() {
		// setup
		$this->Model->Behaviors->load('Media.Attachable', array());
		$this->Model->configureAttachment(array(
				'file' => array(
						'baseDir' => $this->attachmentDir,
						'multiple' => false,
						'removeOnOverwrite' => true,
				)
		), true);

		// save
		$data = array(
			$this->Model->alias => array(
				'title' => 'My Upload',
				'file_upload' => $this->upload1
			)
		);

		$this->Model->create();
		$result = $this->Model->save($data);

		$this->assertTrue(isset($this->Model->id));
		$this->assertEqual($result[$this->Model->alias]['file_upload'], '');
		$this->assertEqual(preg_match('/Upload_File_1_([0-9a-z]+).txt$/', $result[$this->Model->alias]['file']), 1);
		$this->assertTrue(isset($result['Attachment']['file']['path']));
		$this->assertTrue(file_exists($result['Attachment']['file']['path']));

		// read
		$modelId = $this->Model->id;
		$this->Model->create();
		$result2 = $this->Model->read(null, $modelId);

		$this->assertTrue(isset($result2[$this->Model->alias]['file']));
		$this->assertTrue(!isset($result2[$this->Model->alias]['file_upload']));
		$this->assertEqual(preg_match('/Upload_File_1_([0-9a-z]+).txt$/', $result2[$this->Model->alias]['file']), 1);
		$this->assertTrue(isset($result2['Attachment']['file']['path']));
		$this->assertTrue(file_exists($result2['Attachment']['file']['path']));

		// edit
		$data = array(
			$this->Model->alias => array(
				'id' => $modelId,
				'file_upload' => $this->upload2
			)
		);
		$this->Model->create();
		$result3 = $this->Model->save($data);

		$this->assertEqual($this->Model->id, $modelId);
		$this->assertEqual($result3[$this->Model->alias]['file_upload'], '');
		$this->assertEqual(preg_match('/Upload_File_2_([0-9a-z]+).txt$/', $result3[$this->Model->alias]['file']), 1);
		$this->assertTrue(isset($result3['Attachment']['file']['path']));
		$this->assertTrue(file_exists($result3['Attachment']['file']['path']));
		
		//@todo Cleanup on overwrite
		//$this->assertTrue(!file_exists($result2['Attachment']['file']['path']));
		
		//delete
		$deleted = $this->Model->delete($this->Model->id);
		$this->assertTrue($deleted, 'Failed to delete Attachment');
		$this->assertTrue(!file_exists($result3['Attachment']['file']['path']), 'Attachment not deleted');
	}

	public function testSingleWithSubFolders() {
		// setup
		$this->Model->Behaviors->load('Media.Attachable', array());
		$this->Model->configureAttachment(array(
			'file' => array(
				'baseDir' => $this->attachmentDir,
				'multiple' => false,
				'removeOnOverwrite' => true,
				'subDir' => '{MODEL}{DS}'
			)
		), true);

		// save
		$data = array(
			$this->Model->alias => array(
				'title' => 'My Upload with subfolders',
				'file_upload' => $this->upload1
			)
		);

		$this->Model->create();
		$result = $this->Model->save($data);

		$this->assertTrue(is_array($result));
		$this->assertTrue(isset($result['Attachment']['file']['path']));

		$File = new File($result['Attachment']['file']['path'], false);
		$this->assertTrue($File->exists());
		$this->assertTrue($File->folder()->inPath($this->attachmentDir . 'attachable_model' . DS));

		$result = $this->Model->read(null, $this->Model->id);
		debug($result);
	}

	public function testSingleWithSubFolders2() {
		// setup
		$this->Model->Behaviors->load('Media.Attachable', array());
		$this->Model->configureAttachment(array(
				'file' => array(
						'baseDir' => $this->attachmentDir,
						'multiple' => false,
						'removeOnOverwrite' => true,
						'subDir' => '{MODEL}{DS}{MODELID}{DS}'
				)
		), true);

		// save
		$data = array(
			$this->Model->alias => array(
				'title' => 'My Upload with subfolders2',
				'file_upload' => $this->upload1
			)
		);

		$this->Model->create();
		$result = $this->Model->save($data);

		$this->assertTrue(is_array($result));
		$this->assertTrue(isset($result['Attachment']['file']['path']));

		$File = new File($result['Attachment']['file']['path'], false);
		$this->assertTrue($File->exists());
		$this->assertTrue($File->folder()->inPath($this->attachmentDir . 'attachable_model' . DS));

		$result = $this->Model->read(null, $this->Model->id);
		debug($result);
	}

	public function testSingleWithCustomFilename() {
		// setup
		$this->Model->Behaviors->load('Media.Attachable', array());
		$this->Model->configureAttachment(array(
			'file' => array(
				'baseDir' => $this->attachmentDir,
				'multiple' => false,
				'removeOnOverwrite' => false,
				'filename' => '{MODEL}_{MODELID}{DOTEXT}',
				'uniqueFilename' => false,
			)
		), true);

		// save
		$data = array(
			$this->Model->alias => array(
				'title' => 'My Upload with custom filename',
				'file_upload' => $this->upload1
			)
		);

		$this->Model->create();
		$result = $this->Model->save($data);

		$this->assertTrue(is_array($result));
		$this->assertTrue(isset($result['Attachment']['file']['path']));
		$this->assertTrue(file_exists($result['Attachment']['file']['path']));
		$this->assertEquals(basename($result['Attachment']['file']['path']), "attachable_model_" . $this->Model->id . ".txt");
	}


	public function testSingleWithCustomFilenameAndSubfolders() {
		// setup
		$this->Model->Behaviors->load('Media.Attachable', array());
		$this->Model->configureAttachment(array(
			'file' => array(
				'baseDir' => $this->attachmentDir,
				'multiple' => false,
				'removeOnOverwrite' => false,
				'subDir' => '{MODEL}{DS}',
				'filename' => '{MODELID}_{UPLOADNAME}{DOTEXT}',
				'uniqueFilename' => false,
			)
		), true);

		// save
		$data = array(
			$this->Model->alias => array(
				'title' => 'My Upload with custom filename',
				'file_upload' => $this->upload1
			)
		);

		$this->Model->create();
		$result = $this->Model->save($data);

		$this->assertTrue(is_array($result));
		$this->assertTrue(isset($result['Attachment']['file']['path']));
		$this->assertTrue(file_exists($result['Attachment']['file']['path']));
		$this->assertEquals(basename($result['Attachment']['file']['path']), $this->Model->id . "_Upload_File_1.txt");

		//TODO: test if file is in subDir
	}

	public function testMultipleUploadSaveReadDelete() {
		// setup
		$this->Model->Behaviors->load('Media.Attachable', array());
		$this->Model->configureAttachment(array(
			'files' => array(
				'baseDir' => $this->attachmentDir,
				'multiple' => true,
				'removeOnOverwrite' => true,
			)
		), true);
		$data = array(
			$this->Model->alias => array(
				'title' => 'My Upload',
				'files_upload' => array($this->upload1, $this->upload2)
			)
		);

		// save
		$this->Model->create();
		$result = $this->Model->save($data);

		$this->assertTrue(isset($this->Model->id));
		$this->assertEqual($result[$this->Model->alias]['files_upload'], '');
		$this->assertEqual(preg_match('/Upload_File_1_([0-9a-z]+).txt,Upload_File_2_([0-9a-z]+).txt$/', $result[$this->Model->alias]['files']), 1);
		$this->assertTrue(isset($result['Attachment']['files'][0]['path']));
		$this->assertTrue(file_exists($result['Attachment']['files'][0]['path']));
		$this->assertTrue(isset($result['Attachment']['files'][1]['path']));
		$this->assertTrue(file_exists($result['Attachment']['files'][1]['path']));

		// read
		$modelId = $this->Model->id;
		$this->Model->create();
		$result = $this->Model->read(null, $modelId);

		$this->assertTrue(isset($result[$this->Model->alias]['files']));
		$this->assertTrue(!isset($result[$this->Model->alias]['files_upload']));
		$this->assertEqual(preg_match('/Upload_File_1_([0-9a-z]+).txt,Upload_File_2_([0-9a-z]+).txt$/', $result[$this->Model->alias]['files']), 1);
		$this->assertTrue(isset($result['Attachment']['files'][0]['path']));
		$this->assertTrue(file_exists($result['Attachment']['files'][0]['path']));
		$this->assertTrue(isset($result['Attachment']['files'][1]['path']));
		$this->assertTrue(file_exists($result['Attachment']['files'][1]['path']));

		//delete
		$deleted = $this->Model->delete($this->Model->id);
		$this->assertTrue($deleted, 'Failed to delete Attachment');
		$this->assertTrue(!file_exists($result['Attachment']['files'][0]['path']), 'Attachment not deleted');
		$this->assertTrue(!file_exists($result['Attachment']['files'][1]['path']), 'Attachment not deleted');
	}

	public function tearDown() {
		parent::tearDown();

		// clean up test upload dir
		$this->UploadFolder->delete();
		unset($this->UploadFolder);
	}

}

class TestAttachableBehavior extends AttachableBehavior {

	public function getSettings(Model $model) {
		return $this->settings[$model->alias];
	}
}