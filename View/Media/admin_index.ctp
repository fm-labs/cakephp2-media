
<div class="index">
	<h1>Media</h1>
	
	<h2>FileBrowser</h2>
	
	<?php foreach($config['FileBrowser'] as $name => $config):?>
		<?php $urlNew = array('plugin'=>'media','controller'=>'file_browser','action'=>'index',$name);?>
		<?php $urlResume = array('plugin'=>'media','controller'=>'file_browser','action'=>'index',$name,'config'=>$name);?>
		<?php $filebrowserId = 'media-fb-'.$name; ?>
		<dl>
			<dt><?php echo __('Name')?>&nbsp;</dt>
			<dd><?php echo Inflector::humanize($name); ?></dd>
			<dt><?php echo __('Link (New instance)')?>&nbsp;</dt>
			<dd><?php echo $this->Html->link(Router::url($urlNew),$urlNew,array('rel'=>'colorbox')); ?></dd>
			<dt><?php echo __('Link (Resume)')?>&nbsp;</dt>
			<dd><?php echo $this->Html->link(Router::url($urlResume),$urlResume,array('rel'=>'colorbox')); ?></dd>
		</dl>
	<?php endforeach; ?>
	
	<?php 
	// jquery plugin is deprecated
	if (CakePlugin::loaded('Jquery')):
		$this->Js->loadPlugin('JqueryColorbox.JqueryColorbox'); 
		$this->Js->get("a[rel='colorbox']")->plugin('JqueryColorbox')->colorbox(array(
			'width' => '90%',
			'height' => '90%',
			'iframe' => true	
		),true); 
	endif;
?>
</div>
<?php echo $this->Js->writeBuffer(); ?>
