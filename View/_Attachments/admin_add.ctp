<div class="form">
	<h1><?php echo __('Add %s', __('Attachment'));?></h1>
	
	<?php echo $this->Form->create('AttachmentFile'); ?>
	
	<?php 
	echo $this->Form->input('model');
	echo $this->Form->input('ref_id');
	echo $this->Form->input('basename');
	?>
	
	<?php echo $this->Form->end(); ?>
</div>