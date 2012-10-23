<div class="mediaUploads view">
<h2><?php  echo __('Media Upload'); ?></h2>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('Edit %s',__('Media Upload')), array('action' => 'edit', $mediaUpload['MediaUpload']['id'])); ?> </li>
			<li><?php echo $this->Form->postLink(__('Delete %s',__('Media Upload')), array('action' => 'delete', $mediaUpload['MediaUpload']['id']), null, __('Are you sure you want to delete # %s?', $mediaUpload['MediaUpload']['id'])); ?> </li>
			<li><?php echo $this->Html->link(__('List %s',__('Media Uploads')), array('action' => 'index')); ?> </li>
			<li><?php echo $this->Html->link(__('New %s',__('Media Upload')), array('action' => 'add')); ?> </li>
		</ul>
	</div>

	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($mediaUpload['MediaUpload']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Title'); ?></dt>
		<dd>
			<?php echo h($mediaUpload['MediaUpload']['title']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('File'); ?></dt>
		<dd>
			<?php echo h($mediaUpload['MediaUpload']['file']); ?>
			&nbsp;
		</dd>
	</dl>
</div>

<?php debug($mediaUpload); ?>