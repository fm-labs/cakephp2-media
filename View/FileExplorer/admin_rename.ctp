<div class="file-browser">
	<div class="file-browser-cmd">
			<?php 
			echo $this->Form->create();
			?>
			<fieldset>
				<legend><?php echo __d('media',"Rename %s",$this->Form->value('name')); ?></legend>
			<?php
			echo $this->Form->hidden('config');
			echo $this->Form->hidden('dir');
			echo $this->Form->hidden('file');
			echo $this->Form->input('name',array('readonly'=>true,'label'=>__d('media',"Original name")));
			echo $this->Form->input('name_new',array('label'=>__d('media',"New name")));

			echo $this->Form->submit(__d('media',"Rename"));
			?>
			</fieldset>
			<?php $this->Form->end();?>
	</div>
	
	<?php debug($this->Form->data);?>
</div>
