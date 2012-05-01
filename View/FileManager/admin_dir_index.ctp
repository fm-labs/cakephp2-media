<?php $this->Html->css('/media/css/filebrowser',null,array('inline'=>false));?>
<div class="filemanager">
	<h1><?php echo __("FileManager");?></h1>

	<?php if ($fileManager):?>
		<?php echo $this->element('Media.FileManager/layout/default',array(
			'fileManager' => $fileManager,
			'dirCommands' => array(
				'open' => array(
					'title' => __("Open"),
					'url' => array(
						'plugin' => null,
						'controller' => 'test'
					)
				),
				'move',
				'rename',
				'delete'
			),
			'fileCommands' => array('view','edit')
		));?>
	<?php endif; ?>

</div>