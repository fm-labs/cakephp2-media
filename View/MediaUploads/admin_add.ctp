<div class="mediaUploads form">

	<h2><?php echo __('Media Upload'); ?></h2>
	<div class="actions">
		<ul>
	
				<li><?php echo $this->Html->link(__('List %s',__('Media Uploads')), array('action' => 'index')); ?></li>
			</ul>
	</div>
<?php echo $this->Form->create('MediaUpload',array('type'=>'file')); ?>
	<fieldset>
		<legend><?php echo __('Admin Add %s', __('Media Upload')); ?></legend>
	<?php
		echo $this->Form->input('title', array('default'=>'My Single Upload'));
		echo $this->Form->input('file',array('type'=>'file', 'label'=>'Single File Upload'));
		//echo $this->Form->input('file2.',array('type'=>'file', 'label'=>'Multi File Upload', 'multiple'=>'multiple'));
	?>
	</fieldset>
<?php echo $this->Form->button(__('Submit')); ?>
<?php echo $this->Form->end(); ?>


</div>