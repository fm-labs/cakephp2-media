<div class="mediaUploads form">

	<h2><?php echo __('Media Upload'); ?></h2>
	<div class="actions">
		<ul>
	
				<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('MediaUpload.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('MediaUpload.id'))); ?></li>
				<li><?php echo $this->Html->link(__('List %s',__('Media Uploads')), array('action' => 'index')); ?></li>
			</ul>
	</div>
<?php echo $this->Form->create('MediaUpload',array('type'=>'file')); ?>
	<fieldset>
		<legend><?php echo __('Admin Edit %s', __('Media Upload')); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('title');
		echo $this->Form->input('file',array('readonly'=>true));
		echo $this->Form->input('file_upload',array('type'=>'file', 'label'=>'Single File Upload'));
		//echo $this->Form->input('file_upload.',array('type'=>'file', 'label'=>'Multi File Upload', 'multiple'=>'multiple'));
		echo $this->Form->error('file_upload');
	?>
	</fieldset>
<?php echo $this->Form->button(__('Submit')); ?>
<?php echo $this->Form->end(); ?>
</div>