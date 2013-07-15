<?php 
$file = $this->get('file');
$dirUrl = $this->FileExplorer->url('open', $file['dir']);
?>
<?php $this->Html->addCrumb(__('Media'), array('plugin'=>'media','controller'=>'media','action'=>'index')); ?>
<?php $this->Html->addCrumb(__('File Explorer'), array('action'=>'index')); ?>
<?php $this->Html->addCrumb($file['dir'], array('action'=>'index'),array('class'=>'active')); ?>

<div class="view">
	<h1><?php echo $file['name']; ?></h1>
	
	<dl>
		<dt><?php echo __('Size'); ?>&nbsp;</dt>
		<dd><?php echo $file['size']; ?>&nbsp;</dd>
		<dt><?php echo __('Ext'); ?>&nbsp;</dt>
		<dd><?php echo $file['ext']; ?>&nbsp;</dd>
		<dt><?php echo __('Basename'); ?>&nbsp;</dt>
		<dd><?php echo $file['basename']; ?>&nbsp;</dd>
		<dt><?php echo __('Name'); ?>&nbsp;</dt>
		<dd><?php echo $file['name']; ?>&nbsp;</dd>
		<dt><?php echo __('Writable'); ?>&nbsp;</dt>
		<dd><?php echo $file['writable']; ?>&nbsp;</dd>
		<dt><?php echo __('Dir'); ?>&nbsp;</dt>
		<dd><?php echo $this->Html->link($file['dir'],$dirUrl); ?>&nbsp;</dd>
	</dl>
	
	<div>
		<textarea style="width: 98%; min-height: 500px;"><?php echo $file['content']; ?></textarea>
	</div>
	
	<?php debug($file); ?>
</div>