<?php
App::uses('MediaAppModel', 'Media.Model');
/**
 * MediaUpload Model
 *
 */
class MediaUpload extends MediaAppModel {

	public $actsAs = array('Media.Attachable');
	
	public $attachments = array(
		'file' => array()
	);	
	
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'title' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
}
