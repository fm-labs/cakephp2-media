<?php
Router::connect('/m/**',
	array('plugin'=>'media','controller'=>'media_data','action'=>'view'),
	array('pass'=>true)
);

@define('MEDIA_ROUTED',true);