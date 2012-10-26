<?php
class MediaUploadFixture extends CakeTestFixture {

	public $useDbConfig = "test";
	
      public $import = array('model'=>'Media.MediaUpload');
      
      public $records = array(
          array('id' => 1, 'title' => 'Single File', 'file' => 'file1.txt', 'files' => null ),
          array('id' => 2, 'title' => 'No File', 'file' => null, 'files' => null ),
          array('id' => 3, 'title' => 'Multi File', 'file' => null, 'files' => 'file1.txt,file2.txt' ),
          array('id' => 4, 'title' => 'Single Dot File', 'file' => '.dotfile', 'files' => null ),
          array('id' => 5, 'title' => 'Single No Ext File', 'file' => 'filenoext', 'files' => null ),
      );
 }