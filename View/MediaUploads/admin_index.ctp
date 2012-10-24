<?php $this->Helpers->load('Backend.BackendHtml'); ?>
<div class="mediaUploads index">
	<h2><?php echo __('Media Uploads'); ?></h2>
	

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New %s',__('Media Upload')), array('action' => 'add')); ?></li>
			<li><?php echo $this->Html->link(__('New %s',__('Media Upload HTML5')), array('action' => 'add_html5')); ?></li>
			</ul>
	</div>
	
	<table>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('title'); ?></th>
			<th><?php echo $this->Paginator->sort('file'); ?></th>
			<th><?php echo $this->Paginator->sort('files'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
	foreach ($mediaUploads as $mediaUpload): ?>
	<tr>
		<td><?php echo h($mediaUpload['MediaUpload']['id']); ?>&nbsp;</td>
		<td><?php echo h($mediaUpload['MediaUpload']['title']); ?>&nbsp;</td>
		<?php if ($mediaUpload['MediaUpload']['file']):?>
		<td><?php 
			echo h($mediaUpload['MediaUpload']['file'])
			.$this->BackendHtml->iconBool(file_exists($mediaUpload['Attachment']['file'][0]['path'])); ?>
		</td>
		<?php else: ?>
		<td>&nbsp;</td>
		<?php endif; ?>
		
		<?php if ($mediaUpload['MediaUpload']['files']): ?>
		<td><strong><?php echo h($mediaUpload['MediaUpload']['files']); ?></strong><br />
		<?php foreach($mediaUpload['Attachment']['files'] as $attachment): ?>
			<?php echo $attachment['basename'] . $this->BackendHtml->iconBool(file_exists($attachment['path'])); ?>
		<?php endforeach; ?>
		</td>
		<?php else: ?>
		<td>&nbsp;</td>
		<?php endif; ?>
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
