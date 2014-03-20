<?php $this->Html->addCrumb(__('Media'), array('plugin' => 'media','controller' => 'media','action' => 'index')); ?>
<?php $this->Html->addCrumb(__('File Explorer'), array('action' => 'index'),array('class' => 'active')); ?>
<?php $this->Html->addCrumb($this->Form->value('config'), array('action' => 'open','config'=>$this->Form->value('config')),array('class' => 'active')); ?>
<div class="file-browser">
	<div class="file-browser-cmd">
		<?php 
		echo $this->Form->create();
		?>
		<fieldset>
			<legend><?php echo __d('media',"Create File"); ?></legend>
		<?php
		echo $this->Form->input('config');
		echo $this->Form->input('dir');
		echo $this->Form->input('name',array(
			'label'=>__d('media',"Folder name"),
			'default'=>__('New File')
		));

		echo $this->Form->submit(__d('media',"Create File"));
		?>
		</fieldset>
		<?php $this->Form->end();?>
	</div>
	
	<?php debug($this->Form->data);?>
</div>
