<?php $this->Html->css('/media/css/filebrowser',null,array('inline'=>false));?>
<?php $this->Helpers->load('Media.FileBrowser');?>
<?php $this->FileBrowser->set($fileBrowser); ?>

<div class="file_browser">
	<div id="folder_frame" class="frame">
		<?php #echo $this->requestAction($this->FileBrowser->buildUrl(array('cmd'=>'open')),array('return')); ?>
		<?php echo $this->element('FileBrowser/directory/view',compact('fileBrowser'),array('plugin'=>'Media'));?>
		
	</div>
</div>


<div class="file_broser_debug">
	<?php debug($fileBrowser);?>
</div>