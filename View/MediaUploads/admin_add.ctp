<?php $this->Helpers->load('Media.FormUpload'); ?>
<style>
.upload-container {
	border: 3px dashed #CCC;
	padding: 10px;
}

.upload-container:HOVER {
	border-color: #FF9966;
}

.upload-container.dragover {
	border: 5px dashed #FF9966;
}

.upload-queue {
	border: 1px dashed #CCC;
	padding: 7px;
}

.upload-file {
	color: #222;
	padding: 5px;
	margin: 0 0 10px;
	position: relative;
	background-color: #ff6600;
}

.upload-file > div {
	padding: 0 5px;
	display: inline-block;
	margin: 0;
}

.upload-file .upload-file-queueId {
	text-align: right;
}

.upload-file .upload-file-name {
	font-weight: bold;
}

.upload-file .upload-file-size:after {
	content: " byte";
}

.upload-file .upload-file-progress {
	display: block;
	width: 96%;
	margin: 10px 2% 5px;
	height: 10px;
}

.upload-file .upload-file-status {
	margin-left: 20px;
}

.upload-file .upload-file-control {
	top: 5px;
    right: 2%;
    display: block;
    position: absolute;
    text-align: right;
}

.upload-file .upload-file-abort {
	
}

.upload-file.loading .upload-file-abort {
	display: inline;
}

.upload-file.removed {
	background-color: #CCC;
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
		echo $this->FormUpload->input('file_upload',array(
			'multiple' => true
		),array(
			'uploadUrl' => Router::url(array('action'=>'upload_html5')),
		));
		?>
	<?php 
		echo $this->Form->error('file_upload');
	?>
	</fieldset>
<?php echo $this->Form->button(__('Save')); ?>
<?php echo $this->Form->end(); ?>
	
	<br /> <br />
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


<br /> <br />
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


<br /> <br />
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