<ul class="nav nav-list">
	<li class="nav-header">Media Plugin</li>
	<li><?php echo $this->Html->link(__('Media'), array('plugin' => 'media','controller' => 'media','action' => 'index')); ?></li>
	<li><?php echo $this->Html->link(__('FileExplorer'), array('plugin' => 'media','controller' => 'file_explorer','action' => 'index')); ?></li>
	<li><?php echo $this->Html->link(__('MediaUploads'), array('plugin' => 'media','controller' => 'media_uploads','action' => 'index')); ?></li>
	
	<li class="nav-header">Testing</li>
	<li><?php echo $this->Html->link(__('Simple Upload'), array('plugin' => 'media','controller' => 'simple_upload','action' => 'index')); ?></li>
</ul>