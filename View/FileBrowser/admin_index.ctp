<?php echo $this->Html->css('/media/css/filebrowser');?>
<div class="container_16">
	<div class="grid_10">
	<div>
		<h1><?php echo __("Select Image");?></h1>
		<h3><?php echo __("Current Folder: %s",'/'.$fileBrowser['dir']);?></h3>
		<div>
			<ul class="file-browser-list file-browser-folders">
				<li><?php 
					echo $this->Html->tag('span',$this->Html->link('..',array('action'=>$this->params['action'],'dir'=>base64_encode(dirname($fileBrowser['dir'])))),array('class'=>'dir-name')); 
				?></li>
				<?php foreach($fileBrowser['directory_list'] as $folder):?>
				<?php if ($fileBrowser['dir']):?>
				<?php endif; ?>
				<li><?php 
					echo $this->Html->tag('span',$this->Html->link($folder,array('action'=>$this->params['action'],'dir'=>base64_encode($fileBrowser['dir'].$folder.'/'))),array('class'=>'dir-name')); 
				?></li>
				<?php endforeach;?>
			</ul>
		</div>
	
		<div>
			<ul class="file-browser-list file-browser-files">
				<?php foreach($fileBrowser['file_list'] as $file):?>
				<li><?php
					$__fileUrl = $fileBrowser['baseUrl'].$fileBrowser['dir'].$file; 
					echo $this->Html->tag('span',$file,array('class'=>'file-name','title'=>$__fileUrl)); 
					echo $this->Html->tag('span',
						$this->Html->link(__("View"),'#',
							array('class'=>'file-action file-view','data-url'=>$__fileUrl)));
					echo $this->Html->tag('span',
						$this->Html->link(__("Select"),'#',
							array('class'=>'file-action file-select','data-url'=>$__fileUrl)));
				?></li>
				<?php endforeach;?>
			</ul>
		</div>
	</div>
	</div>
	<div class="grid_6">
		<h1>Preview</h1>
		<div id="file-browser-preview">
		
		</div>
	</div>
	<div class="clearfix"></div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	var filebrowserBaseUrl = '<?php echo Router::url('/'); ?>';
	
	$('.file-select').bind('click',function() {
		var value = $(this).data('url');
		console.log('Select File: '+value);
		console.log(parent);
		if (parent == window)
			alert("same same");

		if (parent.$.fn.colorbox == "undefined")
			alert("undefined");
		
		var targetName = parent.$.fn.colorbox.element().data('target');
		alert( targetName );
		/*
		var target = $('#'+targetName, parent.document);
		target.attr('value', value);
		var previewTarget = $('#preview-staff-image',parent.document).attr('src',value);
		*/
		parent.bindSelectedFile(targetName,value);
		parent.$.fn.colorbox.close();
	});
	$('.file-view').bind('click',function() {
		var value = $(this).data('url');
		console.log('View File: '+value);
		var preview = $('<img />',{src: filebrowserBaseUrl + value, alt: value});
		console.log(preview);
		$('#file-browser-preview').html(preview);
	});
});
</script>