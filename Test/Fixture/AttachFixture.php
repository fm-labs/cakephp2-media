<?php
class AttachFixture extends CakeTestFixture {

	public $useDbConfig = "test";
	
      public $fields = array(
          'id' => array('type' => 'integer', 'key' => 'primary'),
          'title' => array('type' => 'string', 'length' => 255, 'null' => false),
          'file' => array('type' => 'string', 'length' => 255, 'null' => true),
          'files' => 'text',
      );
      public $records = array(
          array('id' => 1, 'title' => 'Single File', 'file' => 'file1.txt', 'files' => null ),
          array('id' => 2, 'title' => 'Another Single File', 'file' => 'file2.txt', 'files' => null ),
          array('id' => 3, 'title' => 'Multi File', 'file' => null, 'files' => 'file1.txt,file2.txt' ),
          array('id' => 4, 'title' => 'Single Dot File', 'file' => '.dotfile', 'files' => null ),
          array('id' => 5, 'title' => 'Single No Ext File', 'file' => 'filenoext', 'files' => null ),
      );
 }