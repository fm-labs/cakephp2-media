<?php echo $this->Html->css('/media/css/filebrowser');?>
<?php $this->Helpers->load('Media.FileBrowser');?>
<?php $this->FileBrowser->setFileBrowser($fileBrowser);?>
<div>

	<div class="file-browser" id="file-browser-tabs-1">
		<div class="file-browser-column">
		<div class="file-browser-col1">
		<div>
			<h3><?php echo __d('media',"Current Folder: %s",'/'.$fileBrowser['dir']);?></h3>
			<div>
				<ul class="file-browser-list">
					<li class="dir"><?php 
						echo $this->Html->tag(
							'span',
						$this->Html->link('..',array('action'=>$this->params['action'],'dir'=>base64_encode(dirname($fileBrowser['dir'])))),array('class'=>'dir-name')); 
					?></li>
					<?php foreach($fileBrowser['folders'] as $folder):?>
					<?php if ($fileBrowser['dir']):?>
					<?php endif; ?>
					<li class="dir"><?php 
						echo $this->Html->tag(
							'span',
							$this->Html->link($folder,array('action'=>$this->params['action'],'dir'=>base64_encode($fileBrowser['dir'].$folder.'/'))),array('class'=>'dir-name')); 
					?></li>
					<?php endforeach;?>
					
					<?php foreach($fileBrowser['files'] as $file):?>
					<?php $this->FileBrowser->isImage($file); ?>
					<?php $_fileClass = "file-jpg"?>
					<li class="file <?php echo $_fileClass?>"><?php
						$__fileUrl = $fileBrowser['dir'] . $file;
						echo $this->Html->tag('span', 
							$this->FileBrowser->previewImage($file),
							array('class' => 'file-thumb')
						);
						echo $this->Html->tag('span',$file,array('class'=>'file-name','title'=>$__fileUrl)); 
						echo $this->Html->tag('span',
							$this->Html->link(__d('media',"Rename"),
								array('action'=>$this->params['action'],'cmd'=>'file_rename','dir'=>base64_encode($fileBrowser['dir']),'file'=>base64_encode($file)),
								array('class'=>'file-action file-rename','data-url'=>$__fileUrl)
							)
						);
						echo $this->Html->tag('span',
							$this->Html->link(__d('media',"Delete"),
								array('action'=>$this->params['action'],'cmd'=>'file_delete','dir'=>base64_encode($fileBrowser['dir']),'file'=>base64_encode($file)),
								array('class'=>'file-action file-delete','data-url'=>$__fileUrl),
								__d('media',"Sure, that you want to delete the file '%s'",h($file))
							)
						);
						echo $this->Html->tag('span',
							$this->Html->link(__d('media',"Select"),'#',
								array('class'=>'file-action file-select','data-url'=>$__fileUrl)));
					?></li>
					<?php endforeach;?>
				</ul>
			</div>
		</div>
		</div>
		<div class="file-browser-col2">
			<div class="file-browser-preview">
				<h1>Preview</h1>
				<div class="file-browser-preview">
					<?php echo $this->Html->image('/media/img/filebrowser/_default.jpg', array(
						'id' => 'file-browser-preview-image',
						'alt' => 'Preview',
					));?><br /><br />
					<?php echo $this->Html->link(__d('media',"Large Preview"),'#',array('class'=>'file-browser-btn-preview-large'));?>
					<?php #echo $this->Html->link(__d('media',"Source"),'#',array('class'=>'file-browser-btn-source'));?>
					<?php #echo $this->Html->link(__d('media',"Download"),'#',array('class'=>'file-browser-btn-download'));?>
				</div>
				<?php 
				$this->Js->get('.__file-browser-btn-preview-large')->colorbox(array(
					'rel' => null,
					'inline' => true,
					'href' => 'function() { $(".file-browser-preview-image").attr("src"); }'
				),true);
				?>
			</div>
			<div id="file-browser-upload" class="file-browser-upload">
			<h1>Upload</h1>
				<div class="file-browser-preview-image">
					<?php $uploadDir = ($fileBrowser['dir']) ? $fileBrowser['dir'] : '/'; ?>
					<h1><?php echo __d('media',"Upload to %s", h($uploadDir));?></h1>
					<?php
					    echo $this->Form->create('FileBrowserUpload', array('type' => 'file','url'=>array(
					    	'controller'=>$this->params->controller, 
					    	'action'=>$this->params->action, 
					    	'cmd'=>'upload', 
					    	'dir'=>$fileBrowser['dir_encoded'])
					    ));
					    //html5 multiple file upload - not supported by meio upload yet
					    //echo $this->Form->input('upload_file.', array('type' => 'file', 'multiple'=>'multiple'));
					    echo $this->Form->input('upload_file', array('type' => 'file', 'multiple'=>'multiple'));
					    #echo $this->Form->input('filename', array('type' => 'text','default'=>'test'));
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
	</div>
	<div class="filebrowser-debug">
	<?php debug($fileBrowser);?>
	</div>

</div>
<?php 
/*
$this->Js->get("a[rel='filebrowser']")->colorbox(array(
	'maxWidth' => '90%',
	'maxHeight' => '90%',
),true);
*/
?>
<?php echo $this->Js->writeBuffer(); ?>

<script type="text/javascript">
$(document).ready(function() {
	var filebrowserBaseUrl = '<?php echo $fileBrowser['baseUrl']; ?>';

	$("a[rel='filebrowser']").bind('click',function(e) {
		$("#file-browser-preview-image").attr('src', $(this).attr('href'));
		e.preventDefault();
		return false;
	});

	$(".file-browser-btn-preview-large").bind('click',function() {
		var href = $("#file-browser-preview-image").attr('src');
		$(this).colorbox({
			href: href, 
			open: true,
			maxWidth: '90%',
			maxHeight: '90%',
			rel: 'filebrowser'
		});
		return false;
	});
	
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
