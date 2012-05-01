<?php 
$this->Helpers->load('Media.FileManager');
$this->FileManager->bind($fileManager);
if (!isset($dirCommands))
	$dirCommands = array();
$this->FileManager->bindDirCommands($dirCommands);

if (!isset($fileCommands))
	$fileCommands = array();
$this->FileManager->bindFileCommands($fileCommands);
?>
<div class="folder">

	<h2><?php echo __("Current Folder: %s",$fileManager['dir']);?></h2>
	<ul class="folder">
	<?php if ($fileManager['dir'] != '/'):?>
		<li><span class="dir_parent"><?php
				echo $this->Html->link('..',
					array('action'=>'index','cmd'=>'parent'),
					array()
				);
		?></span>
	<?php endif; ?>
	<?php 
	foreach ($fileManager['folders'] as $dir):
		
		$html = "";
		$html .= $this->Html->tag('span',
			$this->Html->link($dir,$this->FileManager->dirActionUrl($dir,'dir_index')),
			array('class' => 'dir_name')
		);
		
		//apply directory actions
		foreach($this->FileManager->dirCommands as $cmd => $params):
			$html .= $this->Html->tag('span',
				$this->Html->link($params['title'],$this->FileManager->dirActionUrl($dir,$cmd)),
				array('class' => 'dir_'.$cmd)
			);
		endforeach;
		echo $this->Html->tag('li',$html,array('class'=>'directory')); 
	endforeach;
	?>
	</ul>
	
	<ul class="files">
	<?php 
		foreach ($fileManager['files'] as $file):
		
			$html = "";
			$html .= $this->Html->tag('span',
				$this->Html->link($file,$this->FileManager->actionUrl('file','view',$fileManager['dir'],$file)),
				array('class' => 'file_name')
			);
			
			foreach($this->FileManager->fileCommands as $cmd => $params):
				$html .= $this->Html->tag('span',
					$this->Html->link($params['title'],$this->FileManager->fileActionUrl($file,$cmd)),
					array('class' => 'file_'.$cmd)
				);
			endforeach;
			/*
			$html .= $this->Html->tag('span',
				$this->FileManager->actionUrl('file','view',$file),
				array('class' => 'file_view')
			);
			$html .= $this->Html->tag('span',
				$this->FileManager->actionUrl('file','delete',$file),
				//$this->Html->link(__("Delete"),array('cmd'=>'delete','dir'=>$_dir64,'file'=>$_file64),array('data-file'=>$_file)),
				array('class' => 'file_delete')
			);
			*/
			
			$attr = array('class'=>'file');
			echo $this->Html->tag('li',$html,$attr); 
		endforeach;
	?>
	</ul>

	<div class="folder_advanced">
	<br />
		<h4><?php echo __("More folder information");?></h4>
		<dl>
			<dt><?php echo __("Base Path");?>&nbsp;</dt>
			<dd><?php echo $fileManager['basePath']; ?>&nbsp;</dd>
			<dt><?php echo __("Dir");?>&nbsp;</dt>
			<dd><?php echo $fileManager['dir']; ?>&nbsp;</dd>
			<dt><?php echo __("Dir Path");?>&nbsp;</dt>
			<dd><?php echo $fileManager['dirPath']; ?>&nbsp;</dd>
			<dt><?php echo __("Parent Dir");?>&nbsp;</dt>
			<dd><?php echo $fileManager['parentDir']; ?>&nbsp;</dd>
			<dt><?php echo __("Current Time");?>&nbsp;</dt>
			<dd><?php echo date(DATE_COOKIE,time()); ?>&nbsp;</dd>
			
		</dl>
	</div>

</div> <!-- #folder -->

<?php debug($fileManager);?>