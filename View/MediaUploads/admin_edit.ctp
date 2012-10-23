<div class="mediaUploads form">

	<h2><?php echo __('Media Upload'); ?></h2>
	<div class="actions">
		<ul>
	
				<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('MediaUpload.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('MediaUpload.id'))); ?></li>
				<li><?php echo $this->Html->link(__('List %s',__('Media Uploads')), array('action' => 'index')); ?></li>
			</ul>
	</div>
<?php echo $this->Form->create('MediaUpload'); ?>
	<fieldset>
		<legend><?php echo __('Admin Edit %s', __('Media Upload')); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('title');
		echo $this->Form->input('file');
	?>
	</fieldset>
<?php echo $this->Form->button(__('Submit')); ?>
<?php echo $this->Form->end(); ?>
</div>