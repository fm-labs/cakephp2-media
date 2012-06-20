<?php echo $this->Html->css('/media/css/filebrowser');?>
<?php $this->Helpers->load('Media.FileBrowser');?>
<?php $this->FileBrowser->setFileBrowser($fileBrowser);?>
<div>
	<div class="file-browser" id="file-browser-tabs-1">
		<div class="file-browser-actions">
			<div id="file-browser-action-file-rename">
			<?php if ($fileBrowser['FileBrowser']['cmd'] == "file_rename"):?>
				<?php 
				$this->Form->data = $fileBrowser;
				echo $this->Form->create('FileBrowser',array('url'=>$this->FileBrowser->url('file_rename')));
				?>
				<fieldset>
					<legend><?php echo __("Rename %s", $this->Form->value('dir').$this->Form->value('file'))?></legend>
				<?php
				echo $this->Form->hidden('cmd');
				echo $this->Form->hidden('dir');
				echo $this->Form->hidden('file');
				echo $this->Form->input('file_new',array('default'=>$this->Form->value('file')));
				echo $this->Form->submit(__("Rename"));
				?>
				<?php echo $this->Html->link(__("Back to '%s'", $this->Form->value('dir')),$this->FileBrowser->url('open') );?>
				<?php $this->Form->end();?>
				</fieldset>
			<?php elseif ($fileBrowser['FileBrowser']['file']):?>
			<script type="text/javascript">
				$(document).ready(function() {
					$.scrollTo($("#<?php echo md5($fileBrowser['FileBrowser']['file']); ?>"),400);
				});
			</script>
			<?php endif; ?>
			</div>
		</div>
	
		<div class="file-browser-column">
		<!-- COL 1 -->
		<div class="file-browser-col1">
		<div>
			<h3><?php echo __("Current Folder: %s",'/'.$fileBrowser['FileBrowser']['dir']);?></h3>
			<div>
				<ul class="file-browser-list">
				
					<!-- PARENT DIR -->
					<li class="dir"><?php 
						echo $this->Html->tag(
							'span',
							$this->Html->link('..',$this->FileBrowser->url('parent_dir'),array('class'=>'dir-name'))); 
					?></li>
					
					<!-- DIRECTORIES -->
					<?php foreach($fileBrowser['Folder'] as $folder):?>
					<?php if ($fileBrowser['FileBrowser']['dir']):?>
					<?php endif; ?>
					<li class="dir"><?php 
						echo $this->Html->tag(
							'span',
							$this->Html->link($folder,array('action'=>$this->params['action'],'dir'=>base64_encode($fileBrowser['FileBrowser']['dir'].$folder.'/'))),array('class'=>'dir-name')); 
					?></li>
					<?php endforeach;?>
					
					<!-- FILES -->
					<?php foreach($fileBrowser['File'] as $file):?>
					<?php $this->FileBrowser->isImage($file); ?>
					<?php 
					$_fileClass = "file-jpg";
					if ($file == $fileBrowser['FileBrowser']['file'])
						$_fileClass .= " file-highlight";
					?>
					<li id="<?php echo md5($file); ?>" class="file <?php echo $_fileClass?>"><?php
						$__fileUrl = $fileBrowser['FileBrowser']['dir'] . $file;
						echo $this->Html->tag('span', 
							$this->FileBrowser->thumbImage($file),
							array('class' => 'file-thumb')
						);
						echo $this->Html->tag('span',$file,array('class'=>'file-name','title'=>$__fileUrl)); 
						echo $this->Html->tag('span',
							$this->Html->link(__("Rename"),
								//array('action'=>$this->params['action'],'cmd'=>'file_rename','dir'=>base64_encode($fileBrowser['dir']),'file'=>base64_encode($file)),
								$this->FileBrowser->url(array('cmd'=>'file_rename','file'=>$file)),
								array('class'=>'file-action file-rename','data-url'=>$__fileUrl)
							)
						);
						echo $this->Html->tag('span',
							$this->Html->link(__("Delete"),
								//array('action'=>$this->params['action'],'cmd'=>'file_delete','dir'=>base64_encode($fileBrowser['FileBrowser']['dir']),'file'=>base64_encode($file)),
								$this->FileBrowser->url(array('cmd'=>'file_delete','file'=>$file)),
								array('class'=>'file-action file-delete','data-url'=>$__fileUrl),
								__("Sure, that you want to delete the file '%s'",h($file))
							)
						);
						echo $this->Html->tag('span',
							$this->Html->link(__("Select"),'#',
								array('class'=>'file-action file-select','data-url'=>$__fileUrl)));
					?></li>
					<?php endforeach;?>
				</ul>
			</div>
			<div id="file-browser-debug">
			<?php debug($fileBrowser);?>
			</div>
		</div>
		</div>
		<!-- #COL1 -->
		
		
		<!-- COL2 -->
		<div class="file-browser-col2">
			<!-- PREVIEW -->
			<div class="file-browser-preview">
				<h1>Preview</h1>
				<div class="file-browser-preview">
					<?php if ($fileBrowser['FileBrowser']['file']):?>
					<?php $_imgUrl = FULL_BASE_URL . $fileBrowser['FileBrowser']['baseUrl'].$fileBrowser['FileBrowser']['dir'].$fileBrowser['FileBrowser']['file']; ?>
					<?php echo $this->Html->image($_imgUrl, array(
						'id' => 'file-browser-preview-image',
						'alt' => 'Preview',
					));?><br /><br />
					<?php echo $this->Html->link(__("Large Preview"),'#',array('class'=>'file-browser-btn-preview-large'));?>
					<?php #echo $this->Html->link(__("Source"),'#',array('class'=>'file-browser-btn-source'));?>
					<?php #echo $this->Html->link(__("Download"),'#',array('class'=>'file-browser-btn-download'));?>
					<?php else :?>
					<?php echo $this->Html->image('/media/img/filebrowser/_default.jpg', array(
						'id' => 'file-browser-preview-image',
						'alt' => 'Preview',
					));?>
					<?php endif; ?>
				</div>
				<?php 
				$this->Js->get('.__file-browser-btn-preview-large')->colorbox(array(
					'rel' => null,
					'inline' => true,
					'href' => 'function() { $(".file-browser-preview-image").attr("src"); }'
				),true);
				?>
			</div>
			
			<!-- UPLOAD -->
			<div id="file-browser-upload" class="file-browser-upload">
			<h1>Upload</h1>
				<div class="file-browser-preview-image">
					<?php $uploadDir = ($fileBrowser['FileBrowser']['dir']) ? $fileBrowser['FileBrowser']['dir'] : '/'; ?>
					<h1><?php echo __("Upload to %s", h($uploadDir));?></h1>
					<?php
					    echo $this->Form->create('FileBrowserUpload', array(
					    	'type' => 'file',
					    	'url'=>$this->FileBrowser->url('upload')
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
		<!--  #COL2 -->
		<div class="clearfix"></div>
		</div>
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
	var filebrowserBaseUrl = '<?php echo $fileBrowser['FileBrowser']['baseUrl']; ?>';

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
