<small><?php echo $this->Html->link($baseDir,array('action'=>'directory','cmd'=>'open','dir'=>base64_encode($baseDir))); ?></small>
<h2><?php echo $File->name; ?></h2>

<div style="text-align: center;margin-bottom:15px; padding: 10px;">
<?php 
if (in_array($File->ext(),array('jpg','jpeg','png','gif','bmp')) && $File->Folder->inPath(WWW_ROOT)):

	$this->Helpers->load('Media.PhpThumb');
	echo $this->PhpThumb->image($File->pwd(),
		array('w'=>250,'h'=>200,'q'=>70),
		array('alt'=>$File->name(),'title'=>$File->name)
	);
endif;
?>
</div>

<div>
<h4><?php echo __("File information");?></h4>
	<dl>
		<?php foreach($File->info() as $key => $info): ?>
			<dt><?php echo Inflector::humanize($key);?></dt>
			<dd><?php echo $info; ?></dd>
		<?php endforeach; ?>
		<dt><?php echo __("Base Directory");?></dt>
		<dd><?php echo $this->Html->link($baseDir,array('action'=>'directory','cmd'=>'open','dir'=>base64_encode($baseDir))); ?></dd>
		<dt><?php echo __("FilepathEncoded");?></dt>
		<dd><?php echo base64_encode($baseDir); ?></dd>
		<dt><?php echo __("Full Path");?></dt>
		<dd><?php echo $File->pwd(); ?></dd>
		<dt><?php echo __("Exists");?></dt>
		<dd><?php echo $File->exists(); ?></dd>
		<dt><?php echo __("In Webroot");?></dt>
		<dd><?php echo $File->Folder->inPath(WWW_ROOT); ?></dd>
		<dt><?php echo __("Current Time");?></dt>
		<dd><?php echo date(DATE_COOKIE,time()); ?></dd>
	</dl>
</div>
