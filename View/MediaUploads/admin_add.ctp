<?php $this->Html->script('/media/js/uploader',array('inline'=>false)); ?>
		
<style>
.media-uploader-container {
	border: 3px dashed #CCC; 
	padding: 10px;
}

.media-uploader-queue {
	border: 1px dashed #CCC; 
	background-color: #d8d8d8;
}
</style>
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
		echo $this->Form->input('file_upload',array(
			'type'=>'file', 
			'label'=>'Single File Upload',
			'rel' => 'html5upload',
			'id' => 'fileUploadSingleInline',
			'multiple' => true
		));
		?>
	<?php 
		echo $this->Form->error('file_upload');
	?>
	</fieldset>
<?php echo $this->Form->button(__('Save')); ?>
<?php echo $this->Form->end(); ?>

	
	<script>
	var uploader = new Uploader();
	uploader.init({
		uploadUrl: '<?php echo Router::url(array('action'=>'upload_html5'));?>',
		holder: '#fileUploadSingleInline'
	});
	</script>
	
	<br /><br />
<?php echo $this->Form->create('MediaUpload',array('type'=>'file','id'=>'UploadSingle')); ?>
	<fieldset>
		<legend><?php echo __('Single %s', __('Media Upload')); ?></legend>
	<?php
		echo $this->Form->input('title', array('default'=>'Upload'));
		echo $this->Form->input('file_upload',array('type'=>'file', 'label'=>'Single File Upload'));
		echo $this->Form->error('file_upload');
	?>
	</fieldset>
<?php echo $this->Form->button(__('Upload')); ?>
<?php echo $this->Form->end(); ?>


<br /><br />
<?php echo $this->Form->create('MediaUpload',array('type'=>'file','id'=>'UploadMulti')); ?>
	<fieldset>
		<legend><?php echo __('Multi %s', __('Media Upload')); ?></legend>
	<?php
		echo $this->Form->input('title', array('default'=>'Upload'));
		echo $this->Form->input('files_upload.',array('type'=>'file', 'label'=>'Multi File Upload', 'multiple'=>'multiple'));
		echo $this->Form->error('files_upload');
	?>
	</fieldset>
<?php echo $this->Form->button(__('Upload')); ?>
<?php echo $this->Form->end(); ?>


<br /><br />
<?php echo $this->Form->create('MediaUpload',array('type'=>'file','id'=>'UploadCombined')); ?>
	<fieldset>
		<legend><?php echo __('Combined %s', __('Media Upload')); ?></legend>
	<?php
		echo $this->Form->input('title', array('default'=>'Upload'));
		echo $this->Form->input('file_upload',array('type'=>'file', 'label'=>'Single File Upload'));
		echo $this->Form->error('file_upload');
		echo $this->Form->input('files_upload.',array('type'=>'file', 'label'=>'Multi File Upload', 'multiple'=>'multiple'));
		echo $this->Form->error('files_upload');
	?>
	</fieldset>
<?php echo $this->Form->button(__('Upload')); ?>
<?php echo $this->Form->end(); ?>

</div>