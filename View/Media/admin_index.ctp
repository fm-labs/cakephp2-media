<?php $this->Html->addCrumb(__('Media'),array('plugin'=>'media','controller'=>'media','action'=>'index'))?>
<div class="index">
	<h2><?php echo __('Media');?></h2>
	
	<h4><?php echo __('FileExplorer')?></h4>
	<?php if (Configure::read('Media.FileExplorer')):?>
		<?php foreach((array) Configure::read('Media.FileExplorer') as $alias => $config):?>
			<?php $this->FileExplorer->config($alias); ?>
			<h3><?php echo $alias; ?></h3>
			<dl>
				<?php foreach($config as $k => $v):?>
				<dt><?php echo h($k); ?>&nbsp;</dt>
				<dd><?php echo h($v); ?>&nbsp;</dd>
				<?php endforeach; ?>
				<dt><?php echo __('Open'); ?>&nbsp;</dt>
				<dd><?php echo $this->Html->link($this->FileExplorer->actionUrl('open','/')); ?>&nbsp;</dd>
				<dt><?php echo __('Resume'); ?>&nbsp;</dt>
				<dd><?php echo $this->Html->link($this->FileExplorer->actionUrl('open')); ?>&nbsp;</dd>
			</dl>
		<?php endforeach; ?>
	<?php else: ?>
		<div class="alert alert-warning">
		<?php echo __('The Media plugin FileExplorer has not been configured yet'); ?><br />
		<?php echo __('Create APP/Config/media.php'); ?><br /><br />
		</div>
		<pre><?php 
			echo h(file_get_contents(App::pluginPath('Media').DS.'Config'.DS.'media.php.example')); 
		?></pre>
	<?php endif; ?>
	
	<h4><?php echo __('Paths'); ?></h4>
	
	<dl>
		<?php foreach($this->get('paths') as $const => $path): ?>
		<dt><?php echo h($const); ?>&nbsp;</dt>
		<dd><?php echo sprintf("%s [exists: %s][writeable: %s]",$path['path'],$path['exists'],$path['writeable']); ?>&nbsp;</dd>
		<?php endforeach; ?>
	</dl>
	
	<?php foreach(array('urlPaths','flags') as $array): ?>
	<dl>
		<?php foreach($this->get($array) as $const): ?>
		<dt><?php echo h($const); ?>&nbsp;</dt>
		<dd><?php echo (defined($const)) ? constant($const) : __('Undefined'); ?>&nbsp;</dd>
		<?php endforeach; ?>
	</dl>
	<?php endforeach; ?>
</div>