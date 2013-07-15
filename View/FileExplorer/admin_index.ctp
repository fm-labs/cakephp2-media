<div class="index">
	<h1><?php echo __('FileExplorer');?></h1>
	
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
</div>