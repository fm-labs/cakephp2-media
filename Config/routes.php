<?php
App::uses('AjaxplorerRoute','Media.Routing/Route');

Router::connect('/admin/media/ajaxplorer/:action',
	array('plugin'=>'media','prefix'=>'admin','admin'=>true,'controller'=>'ajaxplorer','action'=>'index'),
	array('routeClass'=>'AjaxplorerRoute')	
);

Router::connect('/admin/media/ajaxplorer/plugins/**',
	array('plugin'=>'media','prefix'=>'admin','admin'=>true,'controller'=>'ajaxplorer','action'=>'plugin'),
	array('routeClass'=>'AjaxplorerRoute')	
);
	
?>