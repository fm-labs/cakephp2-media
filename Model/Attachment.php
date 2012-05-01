<?php
class Attachment extends MediaAppModel {
	
	public $actsAs = array('Media.Upload');
	
	public $useTable = 'attachments';
	
	public $upload = array(
		'allowedExt' => array('jpg','jpeg','gif','png'),
		'allowedMime' => array('image/jpg','image/jpeg','image/png'),
		'fileSizeMin' => 0,
		'fileSizeMax' => 2000000,
	);
	
	public $validate = array(
		'title' => array(
			'notempty' => array(
				'rule' => 'notempty',
			)
		),
		'model' => array(
			'notempty' => array(
				'rule' => 'notempty',
				'required' => true
			)
		),
		'ref_id' => array(
			'notempty' => array(
				'rule' => 'notempty',
				'required' => true
			)
		),
		'basename' => array(
			'validateUploadBasename' => array(
				'rule' => array('validateUploadBasename')
			)
		),
		'mime' => array(
			'validateUploadMime' => array(
				'rule' => array('validateUploadMime')
			)
		),
		'ext' => array(
			'validateUploadExtension' => array(
				'rule' => array('validateUploadExtension')
			)
		),
		'size' => array(
			'validateUploadSizeMin' => array(
				'rule' => array('validateUploadSizeMin')
			),
			'validateUploadSizeMax' => array(
				'rule' => array('validateUploadSizeMax')
			)
		),
	);
	
}
?>