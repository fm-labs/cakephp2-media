<?php $this->Html->css('/media/css/filebrowser',null,array('inline'=>false));?>
<div class="folder">
<h2><?php echo __("Current Folder: %s",$baseDir);?></h2>

	<ul class="folder">
		<li><span class="dir_parent"><?php 
			echo $this->Html->link('..',
				array('action'=>'directory','cmd'=>'open','dir'=>base64_encode($parentDir)),
				array('rel'=>$parentDir, 'data-basedir'=>$baseDir));
		?></span>
	<?php 
	$contents = $Folder->read();
	foreach ($contents[0] as $dir):
		
		if (in_array($dir,array('.svn')))
			continue;
	
		$_dir = $baseDir.$dir.'/';
		$_dir64 = base64_encode($_dir);
		$html = "";
		$html .= $this->Html->tag('span',
			$this->Html->link($dir,array('action'=>'directory','cmd'=>'open','dir'=>$_dir64),array('rel'=>$_dir)),
			array('class' => 'dir_name')
		);
		$html .= $this->Html->tag('span',
			$this->Html->link(__("Open"),array('action'=>'directory', 'cmd'=>'open','dir'=>$_dir64),array('rel'=>$_dir)),
			array('class' => 'dir_open')
		);
		$html .= $this->Html->tag('span',
			$this->Html->link(__("Remove"),array('action'=>'directory','cmd'=>'remove','dir'=>$_dir64),array('rel'=>$_dir)),
			array('class' => 'dir_remove')
		);
		echo $this->Html->tag('li',$html,array('class'=>'directory')); 
	endforeach;
	?>
	</ul>
	
	<ul class="files">
	<?php 
		foreach ($contents[1] as $file):
		
			if (in_array($file,array('.htpasswd','.htaccess')))
				continue;
		
			$_file = $baseDir.$file;
			$_file64 = base64_encode($_file);
			$html = "";
			$html .= $this->Html->tag('span',
				$this->Html->link($file,array('action'=>'file','cmd'=>'open','file'=>$_file64),array('rel'=>$_file64)),
				array('class' => 'file_name')
			);
			$html .= $this->Html->tag('span',
				$this->Html->link(__("Open"),array('action'=>'file', 'cmd'=>'view','file'=>$_file64),array('rel'=>$_file64)),
				array('class' => 'file_view')
			);
			$html .= $this->Html->tag('span',
				$this->Html->link(__("Delete"),array('action'=>'file','cmd'=>'delete','file'=>$_file64),array('rel'=>$_file64)),
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
			<dt><?php echo __("Base Dir");?></dt>
			<dd><?php echo $baseDir; ?></dd>
			<dt><?php echo __("Base Dir Encoded");?></dt>
			<dd><?php echo base64_encode($baseDir); ?></dd>
			<dt><?php echo __("Full Path");?></dt>
			<dd><?php echo $Folder->pwd(); ?></dd>
			<dt><?php echo __("In Webroot");?></dt>
			<dd><?php echo $Folder->inPath(WWW_ROOT); ?></dd>
			<dt><?php echo __("Current Time");?></dt>
			<dd><?php echo date(DATE_COOKIE,time()); ?></dd>
			
		</dl>
	</div>

</div>

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