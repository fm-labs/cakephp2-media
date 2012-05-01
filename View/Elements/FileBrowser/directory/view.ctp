<?php $directory =& $fileBrowser->Dir; ?>
<div class="folder">

	<h2><?php echo __("Current Folder: %s",$directory->path());?></h2>
	<ul class="folder">
		<li><span class="dir_parent"><?php 
			echo $this->Html->link('..',
				array('cmd'=>'open','dir'=>base64_encode($directory->parentPath())),
				array('rel'=>$directory->parentPath()));
		?></span>
	<?php 
	$contents = $directory->Folder->read();
	foreach ($contents[0] as $dir):
		
		if (in_array($dir,array('.svn')))
			continue;
	
		$_dir = $directory->path().$dir.'/';
		$_dir64 = base64_encode($_dir);
		$html = "";
		$html .= $this->Html->tag('span',
			$this->Html->link($dir,array('cmd'=>'index','dir'=>$_dir64),array('data-dir'=>$_dir)),
			array('class' => 'dir_name')
		);
		$html .= $this->Html->tag('span',
			$this->Html->link(__("Remove"),array('cmd'=>'remove','dir'=>$_dir64),array('data-dir'=>$_dir)),
			array('class' => 'dir remove')
		);
		echo $this->Html->tag('li',$html,array('class'=>'directory')); 
	endforeach;
	?>
	</ul>
	
	<ul class="files">
	<?php 
		$_dir64 = base64_encode($directory->path());
		foreach ($contents[1] as $file):
		
			if (in_array($file,array('.htpasswd','.htaccess')))
				continue;
			
			$_file = $directory->path().$file;
			$_file64 = base64_encode($_file);
			$html = "";
			$html .= $this->Html->tag('span',
				$this->Html->link($file,array('cmd'=>'view','dir'=>$_dir64,'file'=>$_file64),array('data-file'=>$_file)),
				array('class' => 'file_name')
			);
			$html .= $this->Html->tag('span',
				$this->Html->link(__("View"),array('cmd'=>'view','dir'=>$_dir64,'file'=>$_file64),array('data-file'=>$_file)),
				array('class' => 'file_view')
			);
			$html .= $this->Html->tag('span',
				$this->Html->link(__("Delete"),array('cmd'=>'delete','dir'=>$_dir64,'file'=>$_file64),array('data-file'=>$_file)),
				array('class' => 'file_delete')
			);
			
			$attr = array('class'=>'file');
			echo $this->Html->tag('li',$html,$attr); 
		endforeach;
	?>
	</ul>

	<div class="folder_advanced">
	<br />
		<h4><?php echo __("More folder information");?></h4>
		<dl>
			<dt><?php echo __("Base Dir");?>&nbsp;</dt>
			<dd><?php echo $directory->path(); ?>&nbsp;</dd>
			<dt><?php echo __("Base Dir Encoded");?>&nbsp;</dt>
			<dd><?php echo base64_encode($directory->path()); ?>&nbsp;</dd>
			<dt><?php echo __("Full Path");?>&nbsp;</dt>
			<dd><?php echo $directory->Folder->pwd(); ?>&nbsp;</dd>
			<dt><?php echo __("In Webroot");?>&nbsp;</dt>
			<dd><?php echo $directory->inWebroot(); ?>&nbsp;</dd>
			<dt><?php echo __("Current Time");?>&nbsp;</dt>
			<dd><?php echo date(DATE_COOKIE,time()); ?>&nbsp;</dd>
			
		</dl>
	</div>

</div>

<?php return false; ?>
<?php if ($this->Html->request->is('ajax')): ?>
<script type="text/javascript">
var baseDir = '<?php echo $baseDir; ?>';
$("span.dir_name > a").bind('click',function(e){ 
	var tree = 'ul.jqueryFileTree a[rel="'+$(this).attr('rel')+'"]';
	$(tree).trigger('click');
	e.preventDefault();
	return false;
});
$("span.dir_parent > a").bind('click',function(e){ 
	openDir($(this).attr('rel'));
	$('ul.jqueryFileTree a[rel="'+$(this).attr('data-basedir')+'"]').trigger('click');
	e.preventDefault();
	return false;
});

$("span.file_name > a").bind('click',function(e){ 
	//var tree = 'ul.jqueryFileTree a[rel="'+$(this).attr('rel')+'"]';
	//$(tree).trigger('click');
	previewFile($(this).attr('rel'));
	e.preventDefault();
	return false;
});

</script>
<?php endif; ?>