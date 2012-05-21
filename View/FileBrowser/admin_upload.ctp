<?php $this->extend('AdminPanel.Common/tabs'); ?>
<div>
	<h1><?php echo __("Upload to %s", strval($fileBrowser['dir']));?></h1>
	<?php
		debug($this->Form->validationErrors);
	
	    echo $this->Form->create('Upload', array('type' => 'file'));
	    echo $this->Form->input('file1', array('type' => 'file'));
	    #echo $this->Form->input('filename', array('type' => 'text','default'=>'test'));
	    #echo $this->Form->input('dir', array('type' => 'text'));
	    #echo $this->Form->input('mimetype', array('type' => 'text'));
	    #echo $this->Form->input('filesize', array('type' => 'text'));
	    echo $this->Form->end('Upload');
	?>
</div>