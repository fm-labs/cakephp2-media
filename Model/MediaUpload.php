<?php
App::uses('MediaAppModel', 'Media.Model');
/**
 * MediaUpload Model
 *
 */
class MediaUpload extends MediaAppModel {

	public $actsAs = array('Media.Attachable');
	
	public $attachments = array(
		'file' => array(
			'multiple' => false
		),
		'files' => array(
			'multiple' => true
		)
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
				'message' => 'Upload title missing',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'file' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'File can not be empty',
				//'allowEmpty' => false,
				'required' => true,
				//'last' => false, // Stop validation after this rule
				'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
}
