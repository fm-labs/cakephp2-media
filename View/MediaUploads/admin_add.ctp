<?php $this->Helpers->load('Media.FormUpload'); ?>
<div class="mediaUploads form">

	<h2><?php echo __('Media Upload'); ?></h2>
	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('List %s',__('Media Uploads')), array('action' => 'index')); ?></li>
		</ul>
	</div>

<?php echo $this->Form->create('MediaUpload',array('type'=>'file','id'=>'UploadSingleInline')); ?>
	<fieldset>
		<legend><?php echo __('Single %s Inline', __('Media Upload')); ?></legend>
	<?php
		echo $this->Form->input('title', array('default'=>'Upload'));
	?>
		<?php 
		echo $this->FormUpload->input('MediaUpload.file_upload',array(
			'multiple' => true
		),array(
			'uploadUrl' => Router::url(array('action'=>'upload_html5')),
		));
		?>
	<?php 
		echo $this->Form->error('file_upload');
		echo $this->Form->input('file');
		echo $this->Form->error('file');
	?>
	</fieldset>
<?php echo $this->Form->button(__('Save')); ?>
<?php echo $this->Form->end(); ?>
<?php debug($this->Form->data);?>

	<br /> <br />
<?php echo $this->Form->create('MediaUpload',array('type'=>'file','id'=>'UploadSingle')); ?>
	<fieldset>
		<legend><?php echo __('Single %s', __('Media Upload')); ?></legend>
	<?php
		echo $this->Form->input('title', array('default'=>'Upload'));
		echo $this->Form->input('file_upload',array('type'=>'file', 'label'=>'Single File Upload'));
		echo $this->Form->error('file_upload');
		echo $this->Form->error('file');
	?>
	</fieldset>
<?php echo $this->Form->button(__('Upload')); ?>
<?php echo $this->Form->end(); ?>


<br /> <br />
<?php echo $this->Form->create('MediaUpload',array('type'=>'file','id'=>'UploadMulti')); ?>
	<fieldset>
		<legend><?php echo __('Multi %s', __('Media Upload')); ?></legend>
	<?php
		echo $this->Form->input('title', array('default'=>'Upload'));
		echo $this->Form->input('files_upload.',array('type'=>'file', 'label'=>'Multi File Upload', 'multiple'=>'multiple'));
		echo $this->Form->error('files_upload');
		echo $this->Form->error('file');
	?>
	</fieldset>
<?php echo $this->Form->button(__('Upload')); ?>
<?php echo $this->Form->end(); ?>


<br /> <br />
<?php echo $this->Form->create('MediaUpload',array('type'=>'file','id'=>'UploadCombined')); ?>
	<fieldset>
		<legend><?php echo __('Combined %s', __('Media Upload')); ?></legend>
	<?php
		echo $this->Form->input('title', array('default'=>'Upload'));
		echo $this->Form->input('file_upload',array('type'=>'file', 'label'=>'Single File Upload'));
		echo $this->Form->error('file_upload');
		echo $this->Form->error('file');
		
		echo $this->Form->input('files_upload.',array('type'=>'file', 'label'=>'Multi File Upload', 'multiple'=>'multiple'));
		echo $this->Form->error('files_upload');
		echo $this->Form->error('files');
	?>
	</fieldset>
<?php echo $this->Form->button(__('Upload')); ?>
<?php echo $this->Form->end(); ?>

</div>