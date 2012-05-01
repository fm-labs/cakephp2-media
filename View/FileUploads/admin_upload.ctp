<div class="fileUploads add">
	<h2><?php echo __("FileUpload")?></h2>

	<fieldset>
		<legend><?php echo __("Single Upload - Standalone");?></legend>
		<p><?php echo __("Upload a single file to folder");?></p>
		<?php 
		echo $this->Form->create("Attachment",array(
			'type'=>'file',
			'url'=>array('controller'=>'file_uploads', 'action'=>'standalone'))
		);
		echo $this->Form->input("Attachment.ref_id",array('type'=>'text','value'=>1));
		echo $this->Form->input('Attachment.upload_title',array('default'=>'dateiname'));
		echo $this->Form->input('Attachment.upload_file',array('type'=>'file'));
		#echo $this->Form->input('Attachment.1.upload_file',array('type'=>'file'));
		echo $this->Form->end(__("Upload File"));
		?>
	</fieldset>
	
	<fieldset>
		<legend><?php echo __("Simple Upload for Model");?></legend>
		<p><?php echo __("Upload a single file to folder");?></p>
		<?php 
		echo $this->Form->create("Upload",array('type'=>'file'));
		echo $this->Form->input("Upload.id");
		echo $this->Form->input('Attachment.upload_title',array('default'=>'dateiname'));
		echo $this->Form->input('Attachment.upload_file',array('type'=>'file'));
		#echo $this->Form->input('Attachment.1.upload_file',array('type'=>'file'));
		echo $this->Form->end(__("Upload File"));
		?>
	</fieldset>
	
	
	<br /><br />
	<fieldset>
		<legend><?php echo __("Simple Upload (multi)");?></legend>
		<p><?php echo __("Upload a single file to folder");?></p>
		<?php 
		echo $this->Form->create("Upload",array('type'=>'file'));
		echo $this->Form->input('Attachment.0.upload_title',array('default'=>'dateiname'));
		echo $this->Form->input('Attachment.0.upload_file',array('type'=>'file'));
		echo $this->Form->input('Attachment.1.upload_title',array('default'=>'dateiname2'));
		echo $this->Form->input('Attachment.1.upload_file',array('type'=>'file'));
		echo $this->Form->end(__("Upload Files"));
		?>
	</fieldset>
	
	<br /><br />
	<fieldset>
		<legend><?php echo __("Attachement Upload (multi)");?></legend>
		<p><?php echo __("Upload a single file to folder");?></p>
		<?php 
		echo $this->Form->create("AttachmentMulti",array('type'=>'file'));
		echo $this->Form->input('Attachment.0.file',array('type'=>'file'));
		echo $this->Form->input('Attachment.1.file',array('type'=>'file'));
		echo $this->Form->end(__("Upload Attachment"));
		?>
	</fieldset>

	<div class="clearfix"></div>

</div>