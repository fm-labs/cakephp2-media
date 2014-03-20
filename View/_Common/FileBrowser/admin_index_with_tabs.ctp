<?php echo $this->Html->css('/media/css/filebrowser');?>
<?php $this->Helpers->load('Jquery.JqueryUi');?>

<?php
$_id = uniqid('file-browser-tabs');

?>
<div id="<?php echo $_id ?>">

	<ul>
		<li><?php echo $this->Html->link(__d('media',"FileBrowser"),'#file-browser-tabs-1'); ?></li>
		<li><?php echo $this->Html->link(__d('media',"Upload"),'#file-browser-tabs-2'); ?></li>
	</ul>

	<div class="file-browser" id="file-browser-tabs-1">
		<div class="file-browser-col1">
		<div>
			<h1><?php echo __d('media',"Select Image");?></h1>
			<h3><?php echo __d('media',"Current Folder: %s",'/'.$fileBrowser['dir']);?></h3>
			<div>
				<ul class="file-browser-list">
					<li class="dir"><?php 
						echo $this->Html->tag('span',$this->Html->link('..',array('action'=>$this->params['action'],'dir'=>base64_encode(dirname($fileBrowser['dir'])))),array('class' => 'dir-name'));
					?></li>
					<?php foreach($fileBrowser['directory_list'] as $folder):?>
					<?php if ($fileBrowser['dir']):?>
					<?php endif; ?>
					<li class="dir"><?php 
						echo $this->Html->tag('span',$this->Html->link($folder,array('action'=>$this->params['action'],'dir'=>base64_encode($fileBrowser['dir'].$folder.'/'))),array('class' => 'dir-name'));
					?></li>
					<?php endforeach;?>
					
					<?php foreach($fileBrowser['file_list'] as $file):?>
					<li class="file"><?php
						$__fileUrl = $fileBrowser['dir'].$file; 
						echo $this->Html->tag('span',$file,array('class' => 'file-name','title'=>$__fileUrl));
						echo $this->Html->tag('span',
							$this->Html->link(__d('media',"View"),'#',
								array('class' => 'file-action file-view','data-url'=>$__fileUrl)));
						echo $this->Html->tag('span',
							$this->Html->link(__d('media',"Select"),'#',
								array('class' => 'file-action file-select','data-url'=>$__fileUrl)));
					?></li>
					<?php endforeach;?>
				</ul>
			</div>
		</div>
		</div>
		<div class="file-browser-col2">
			<div id="file-browser-preview" class="file-browser-preview">
			<h1>Preview</h1>
				<div class="file-browser-preview-image">
					<?php echo __d('media',"No file selected"); ?>
				</div>
			</div>
			<div id="file-browser-upload" class="file-browser-upload">
			<h1>Preview</h1>
				<div class="file-browser-preview-image">
					<?php $uploadDir = ($fileBrowser['dir']) ? $fileBrowser['dir'] : '/'; ?>
					<h1><?php echo __d('media',"Upload to %s", h($uploadDir));?></h1>
					<?php
					    echo $this->Form->create('FileBrowserUpload', array('type' => 'file','url'=>array(
					    	'controller'=>$this->params->controller, 
					    	'action'=>$this->params->action, 
					    	'cmd' => 'upload',
					    	'dir'=>$fileBrowser['dir_encoded'])
					    ));
					    //html5 multiple file upload - not supported by meio upload yet
					    //echo $this->Form->input('upload_file.', array('type' => 'file', 'multiple' => 'multiple'));
					    echo $this->Form->input('upload_file', array('type' => 'file', 'multiple' => 'multiple'));
					    #echo $this->Form->input('filename', array('type' => 'text','default' => 'test'));
					    #echo $this->Form->input('dir', array('type' => 'text'));
					    #echo $this->Form->input('mimetype', array('type' => 'text'));
					    #echo $this->Form->input('filesize', array('type' => 'text'));
					    echo $this->Form->end('Upload');
					?>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>


	<div id="file-browser-tabs-2">
		<div>
			<?php $uploadDir = ($fileBrowser['dir']) ? $fileBrowser['dir'] : '/'; ?>
			<h1><?php echo __d('media',"Upload to %s", h($uploadDir));?></h1>
			<?php
			    echo $this->Form->create('FileBrowserUpload', array('type' => 'file','url'=>array(
			    	'controller'=>$this->params->controller, 
			    	'action'=>$this->params->action, 
			    	'cmd' => 'upload',
			    	'dir'=>$fileBrowser['dir_encoded'])
			    ));
			    //html5 multiple file upload - not supported by meio upload yet
			    //echo $this->Form->input('upload_file.', array('type' => 'file', 'multiple' => 'multiple'));
			    echo $this->Form->input('upload_file', array('type' => 'file', 'multiple' => 'multiple'));
			    #echo $this->Form->input('filename', array('type' => 'text','default' => 'test'));
			    #echo $this->Form->input('dir', array('type' => 'text'));
			    #echo $this->Form->input('mimetype', array('type' => 'text'));
			    #echo $this->Form->input('filesize', array('type' => 'text'));
			    echo $this->Form->end('Upload');
			?>
		</div>
	</div>
	<div style="clear:both;"></div>

</div>
<?php
$this->Js->get('#'.$_id);
$script = $this->JqueryUi->tabs();
echo $this->Html->scriptBlock($script);
?>
<script type="text/javascript">
$(document).ready(function() {
	var filebrowserBaseUrl = '<?php echo $fileBrowser['baseUrl']; ?>';
	
	$('.file-select').bind('click',function() {
		var value = $(this).data('url');

		if (parent == window || parent.$.fn.colorbox == "undefined") {
			return;
		} else {
			var targetName = parent.$.fn.colorbox.element().data('target');
			var target = $('#'+targetName, parent.document);
			target.attr('value', value);

			var targetPreviewName = target.data('preview');
			var targetPreview = $('#'+targetPreviewName, parent.document);
			var preview = $('<img />',{
				src: filebrowserBaseUrl + value, 
				alt: value, 
				width: '100px'
			});
			console.log(preview);
			targetPreview.html(preview);
			
			parent.$.fn.colorbox.close();
		}
	});
	$('.file-view').bind('click',function() {
		var value = $(this).data('url');
		console.log('View File: '+value);
		console.log('Preview Url: '+filebrowserBaseUrl+value);
		var preview = $('<img />',{
			src: filebrowserBaseUrl + value, 
			alt: value, 
		});
		console.log(preview);
		$('#file-browser-preview').html(preview);
	});
});
</script>