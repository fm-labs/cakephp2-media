<?php $this->Html->addCrumb(__('Media'),array('plugin'=>'media','controller'=>'media','action'=>'index'))?>
<div class="index">
	<h2><?php echo __('Media');?></h2>
	
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
				<dd><?php echo $this->Html->link($this->FileExplorer->url('open','/')); ?>&nbsp;</dd>
				<dt><?php echo __('Resume'); ?>&nbsp;</dt>
				<dd><?php echo $this->Html->link($this->FileExplorer->url('open')); ?>&nbsp;</dd>
			</dl>
		<?php endforeach; ?>
	<?php else: ?>
		<?php echo __('The media plugin has not been configured yet'); ?><br />
		<?php echo __('Create APP/Config/media.php'); ?><br /><br />
		<pre><?php 
			echo h(file_get_contents(App::pluginPath('Media').DS.'Config'.DS.'media.php.example')); 
		?></pre>
	<?php endif; ?>
</div>