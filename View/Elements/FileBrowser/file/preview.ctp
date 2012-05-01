<?php $resource =& $fileBrowser->Resource; ?>
<?php if (!$resource->exists()):?>
	<h2><?php echo __("No file selected");?></h2>
	<?php return false; ?>
<?php endif; ?>

<?php $dirPath = $resource->Dir->path(); ?>
<small><?php echo $this->Html->link($dirPath,array('cmd'=>'open','dir'=>base64_encode($dirPath))); ?></small>
<h2><?php echo $resource->File->name; ?></h2>

<div style="text-align: center;margin-bottom:15px; padding: 10px;">
<?php 
if (in_array($resource->File->ext(),array('jpg','jpeg','png','gif','bmp')) && $resource->File->Folder->inPath(WWW_ROOT)):

	$this->Helpers->load('Media.PhpThumb');
	echo $this->PhpThumb->image($resource->File->pwd(),
		array('w'=>250,'h'=>200,'q'=>70),
		array('alt'=>$resource->File->name(),'title'=>$resource->File->name)
	);
endif;
?>
</div>

<div>
<h4><?php echo __("File information");?></h4>
	<dl>
		<?php foreach($resource->File->info() as $key => $info): ?>
			<dt><?php echo Inflector::humanize($key);?></dt>
			<dd><?php echo $info; ?></dd>
		<?php endforeach; ?>
		<dt><?php echo __("Base Directory");?></dt>
		<dd><?php echo $this->Html->link($dirPath,array('cmd'=>'open','dir'=>base64_encode($dirPath))); ?></dd>
		<dt><?php echo __("FilepathEncoded");?></dt>
		<dd><?php echo base64_encode($dirPath); ?></dd>
		<dt><?php echo __("Full Path");?></dt>
		<dd><?php echo $resource->File->pwd(); ?></dd>
		<dt><?php echo __("Exists");?></dt>
		<dd><?php echo $resource->File->exists(); ?></dd>
		<dt><?php echo __("In Webroot");?></dt>
		<dd><?php echo $resource->File->Folder->inPath(WWW_ROOT); ?></dd>
		<dt><?php echo __("Current Time");?></dt>
		<dd><?php echo date(DATE_COOKIE,time()); ?></dd>
	</dl>
</div>
