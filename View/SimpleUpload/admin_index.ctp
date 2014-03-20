<div class="index">
	<h2>Simple Upload - Single</h2>
	
	<?php echo $this->Form->create(null, array('type' => 'file'))?>
	<?php echo $this->Form->input('uploadfile',array('type' => 'file'))?>
	<?php echo $this->Form->input('extra',array('default' => 'extravalue'));?>
	<?php echo $this->Form->button(__('Upload')); ?>
	<?php echo $this->Form->end();?>
	
	<hr />
	<h2>Simple Upload - Multiple</h2>
	
	<?php echo $this->Form->create(null, array('type' => 'file'))?>
	<?php echo $this->Form->input('uploadfile.',array('type' => 'file','multiple'=>true))?>
	<?php echo $this->Form->input('extra',array('default' => 'extravalue'));?>
	<?php echo $this->Form->button(__('Upload')); ?>
	<?php echo $this->Form->end();?>
</div>