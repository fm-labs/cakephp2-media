<?php 
/**
 * @property FileExplorerHelper $FileExplorer
 * @property HtmlHelper $HtmlHelper
 */
?>
<?php $this->Html->addCrumb(__('Media'), array('plugin'=>'media','controller'=>'media','action'=>'index')); ?>
<?php $this->Html->addCrumb(__('File Explorer'), array('action'=>'index'),array('class'=>'active')); ?>
<?php $this->Html->css('/media/css/fileexplorer',null,array('inline'=>true)); ?>

<?php 
$fe = $this->get('fe');
$_contents = $fe['contents'];
$_dir = $fe['dir'];
$_parentDir = $fe['parent_dir'];
?>

<div class="index">
	
	<div class="file-browser">
		<div class="file-browser-column">
		<!-- COL 1 -->
		<div class="file-browser-col1">
		<div class="inner">
			<h4><?php echo __d('media',"Current Folder: %s",$_dir);?>&nbsp;
			<small><?php echo $this->Html->link(__d('media',"Reload"),$this->FileExplorer->url('open',$_dir));?></small>
			</h4>
			<p><?php echo ($fe['writeable']) ? __("Writeable") : __("Not writeable"); ?></p>
			<div class="actions">
				<ul>
					<li><?php echo $this->Html->link(__('New %s',__('Folder')),$this->FileExplorer->url('create',$_dir)); ?></li>
					<li><?php echo $this->Html->link(__('New %s',__('File')),$this->FileExplorer->url('add',$_dir)); ?></li>
				</ul>
			</div>
	
			<div>
				<div class="file-browser-list">
					<!-- DIRECTORIES -->
					<table class="file-browser-list">
						<tr>
							<th class="name"><?php echo __('Name'); ?></th>
							<th class="writeable"><?php echo __('Writable'); ?></th>
							<th class="actions"><?php echo __('Actions'); ?></th>
						</tr>
						<!-- CURRENT DIR -->
						<tr class="folder current">
							<td class="name"><?php 
								echo $this->Html->link('.',$this->FileExplorer->url('open',$_dir)); 
							?></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<!-- PARENT DIR -->
						<tr class="folder parent">
							<td class="name"><?php 
								echo $this->Html->link('..', $this->FileExplorer->url('open',$_parentDir));?></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<?php if ($_contents['Folder']):?>
						<?php foreach($_contents['Folder'] as $folder):?>
						<tr class="folder">
							<td class="name"><?php 
									echo $this->Html->link($folder['name'], $this->FileExplorer->url('open',$_dir.$folder['name']));
							?>
							</td>
							<td><?php echo ($folder['writeable']) ? "yes" : "no"; ?></td>
							<td class="actions">
								<?php echo $this->Html->link(__('Open'),
										$this->FileExplorer->url('open',$_dir.$folder['name']),
										array('class'=>'btn-primary'));?>
								<?php echo $this->Html->link(__('Move'),$this->FileExplorer->url('move',$_dir.$folder['name']));?>
							</td>
						</tr>
						<?php endforeach;?>
						<?php endif; ?>
					
					<!-- FILES -->
						<?php if ($_contents['File']):?>
						<?php foreach($_contents['File'] as $file):?>
						<tr class="file" id="<?php echo md5($file['basename']); ?>">
							<td class="name"><?php echo $file['basename']; ?></td>
							<td><?php echo ($file['writeable']) ? "yes" : "no"; ?></td>
							<td class="actions">
								<?php echo $this->Html->link(__d('media',"View"),
										$this->FileExplorer->url('view',$_dir,$file['basename']),
										array()
									); ?>
								<?php echo $this->Html->link(__d('media',"Edit"),
										$this->FileExplorer->url('edit',$_dir,$file['basename']),
										array()
									); ?>
									<?php echo $this->Html->link(__d('media',"Rename"),
										$this->FileExplorer->url('rename',$_dir,$file['basename']),
										array()
									); ?>
									<?php echo $this->Html->link(__d('media',"Copy"),
										$this->FileExplorer->url('copy',$_dir,$file['basename']),
										array()
									); ?>
									<?php echo $this->Html->link(__d('media',"Delete"),
										$this->FileExplorer->url('delete',$_dir,$file['basename']),
										array(),
										__d('media',"Sure, that you want to delete the file '%s'",h($file['basename']))
									); ?>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php endif; ?>
					</table>
				</div>
				
				<div id="file-browser-debug">
				</div>
			</div>
		</div>
		</div>
		<!-- #COL1 -->
		
		
		<!-- COL2 -->
		<!--  #COL2 -->
		<div class="clearfix"></div>
		
		
		</div>
	</div>	
	<?php debug($fe); ?>
</div>