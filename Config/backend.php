<?php
/**
 * Default Backend config
 */
$config = array(
	'Backend' => array(
		'Navigation' => array(
			'media' => array(
				'media' => array(
					'title'	=> __("Media"),
					'url' 	=> array('plugin' => 'media','controller' => 'media','action' => 'index'),
					'actions' => array(
						array(__('FileExplorer'), array('plugin' => 'media','controller' => 'file_explorer','action' => 'index'),null),
						array(__('FileBrowser (deprecated)'), array('plugin' => 'media','controller' => 'file_browser','action' => 'index'),null),
						array(__('Attachments'), array('plugin' => 'media','controller' => 'attachments','action' => 'index'),null),
					)
				),
			)
		)	
	)
);
?>