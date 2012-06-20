<div>
	<h1><?php __("Rename file")?></h1>
	<?php echo $this->Form->create('FileBrowser'); ?>
	<?php 
		echo $this->Form->input('file');
		echo $this->Form->input('file_encoded');
		echo $this->Form->input('cmd');
	?>
</div>