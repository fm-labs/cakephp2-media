<div class="file-browser">
	<div class="file-browser-cmd">
		<?php 
		echo $this->Form->create();
		?>
		<fieldset>
			<legend><?php echo __d('media',"Move %s",$this->Form->value('dir')); ?></legend>
		<?php
		echo $this->Form->input('config');
		echo $this->Form->input('dir',array(
				'readonly'=>true,
				'label'=>__d('media',"From")
		));
		echo $this->Form->input('dir_new',array(
			'label'=>__d('media',"To")));

		echo $this->Form->submit(__d('media',"Move"));
		?>
		</fieldset>
		<?php $this->Form->end();?>
	</div>
	
	<?php debug($this->Form->data);?>
</div>
