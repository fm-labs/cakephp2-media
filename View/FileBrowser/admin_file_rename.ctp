<?php try {?>
<?php $this->Helpers->load('Media.FileBrowser'); ?>
<?php $this->FileBrowser->setFileBrowser($fileBrowser); ?>
<div class="file-browser">
	<div class="file-browser-cmd">
			<?php 
			$this->Form->data = $fileBrowser;
			echo $this->Form->create('FileBrowser',array('url'=>$this->FileBrowser->url('file_rename')));
			?>
			<fieldset>
				<legend><?php echo __d('media',"Rename %s", $this->Form->value('dir').$this->Form->value('file'))?></legend>
			<?php
			echo $this->Form->hidden('cmd');
			echo $this->Form->hidden('dir');
			echo $this->Form->input('file',array('readonly'=>true,'label'=>__d('media',"Original name")));
			echo $this->Form->input('file_new',array('label'=>__d('media',"New name"), 'default'=>$this->Form->value('file')));
			echo $this->Form->submit(__d('media',"Rename"));
			?>
			<?php echo $this->Html->link(__d('media',"Back to '/%s'", $this->Form->value('dir')),$this->FileBrowser->url('open') );?>
			</fieldset>
			<?php $this->Form->end();?>
	</div>
</div>
<?php } catch(Exception $e) {
	echo $e->getMessage();
}?>