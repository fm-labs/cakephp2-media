<?php $this->Helpers->load('Backend.BackendHtml'); ?>
<div class="mediaUploads index">
	<h2><?php echo __('Media Uploads'); ?></h2>
	

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New %s',__('Media Upload')), array('action' => 'add')); ?></li>
			</ul>
	</div>
	
	<table>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('title'); ?></th>
			<th><?php echo $this->Paginator->sort('file'); ?></th>
			<th><?php echo __('Exists') ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
	foreach ($mediaUploads as $mediaUpload): ?>
	<tr>
		<td><?php echo h($mediaUpload['MediaUpload']['id']); ?>&nbsp;</td>
		<td><?php echo h($mediaUpload['MediaUpload']['title']); ?>&nbsp;</td>
		<td><?php echo h($mediaUpload['MediaUpload']['file']); ?>&nbsp;</td>
		<td><?php echo $this->BackendHtml->iconBool(file_exists($mediaUpload['Attachment']['file']['path'])); ?></td>
		<td class="actions">
		<ul class="actions">
			<li><?php echo $this->Html->link(__('View'), array('action' => 'view', $mediaUpload['MediaUpload']['id'])); ?></li>
			<li><?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $mediaUpload['MediaUpload']['id'])); ?></li>
			<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $mediaUpload['MediaUpload']['id']), null, __('Are you sure you want to delete # %s?', $mediaUpload['MediaUpload']['id'])); ?></li>
		</ul>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<?php echo $this->element('Backend.pagination/default'); ?>		
</div>
<?php debug($mediaUploads); ?>
