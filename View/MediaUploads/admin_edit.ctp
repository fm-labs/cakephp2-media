<?php $this->Helpers->load('Media.FormUpload'); ?>
<?php $this->Helpers->load('Media.Attachment'); ?>
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
	?>
	</fieldset>
	
	<fieldset>
		<legend>File</legend>
		<?php 
		//echo $this->Form->input('file',array('readonly'=>true, 'type'=>'hidden'));
		
		echo $this->Attachment->preview('MediaUpload.file');
		echo $this->FormUpload->input('MediaUpload.file',array(
			'type'=>'file', 
			'label'=>'Single File Upload',
			//'before' => $this->Attachment->preview('MediaUpload.file'),
		));
		echo $this->Form->error('file');
		?>
	</fieldset>
	
	<fieldset>
		<legend>Files</legend>
		<?php 
		echo $this->Form->input('files',array('readonly'=>true));
		echo $this->FormUpload->input('MediaUpload.files',array('type'=>'file', 'multiple'=>'multiple', 'label'=>'Multi File Upload'));
		echo $this->Form->error('files');
		echo $this->Attachment->preview('MediaUpload.files');
		//echo $this->Attachment->preview('MediaUpload.files',null,'small');
		//echo $this->Attachment->preview('MediaUpload.files',null, 'big');
		?>
	?>
	</fieldset>
<?php echo $this->Form->button(__('Submit')); ?>
<?php echo $this->Form->end(); ?>
</div>
<?php debug($this->Form->data); ?>