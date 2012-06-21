<?php $this->Helpers->load('Media.FileBrowser');?>
<?php $this->FileBrowser->setFileBrowser($fileBrowser);?>
<?php if ($fileBrowser['FileBrowser']['file']):?>
<script type="text/javascript">
	$(document).ready(function() {
		$.scrollTo($("#<?php echo md5($fileBrowser['FileBrowser']['file']); ?>"),500,{
			offset: {top: -300}
		});

		$(".canhide").click(function() {
			$(this).fadeOut('300');
		});
	});
</script>
<?php endif; ?>
<div class="file-browser">
	<div id="file-browser-tabs-1">
		<div class="file-browser-column">
		<!-- COL 1 -->
		<div class="file-browser-col1">
		<div>
			<h3><?php echo __("Current Folder: %s",'/'.$fileBrowser['FileBrowser']['dir']);?>&nbsp;
			<?php echo $this->Html->link(__("Reload"),$this->Html->url(null,true));?>
			</h3>
			<div>
				<div class="file-browser-list">
				
					<!-- PARENT DIR -->
					<div class="list-item dir"><?php 
						echo $this->Html->tag(
							'span',
							$this->Html->link('..',$this->FileBrowser->url('parent_dir'),array('class'=>'dir-name'))); 
					?></div>
					
					<!-- DIRECTORIES -->
					<?php foreach($fileBrowser['Folder'] as $folder):?>
					<?php if ($fileBrowser['FileBrowser']['dir']):?>
					<?php endif; ?>
					<div class="list-item dir"><?php 
						echo $this->Html->tag(
							'span',
							$this->Html->link($folder,$this->FileBrowser->url(array('cmd'=>'open','dir'=>$folder.'/','file'=>null)),array('class'=>'dir-name')));
					?></div>
					<?php endforeach;?>
					
					<!-- FILES -->
					<?php foreach($fileBrowser['File'] as $file):?>
					<?php $this->FileBrowser->isImage($file); ?>
					<?php 
					$_fileClass = "list-item file";
					if ($file == $fileBrowser['FileBrowser']['file'])
						$_fileClass .= " file-highlight";
					?>
					<div id="<?php echo md5($file); ?>" class="file <?php echo $_fileClass?>"><?php
						$__fileUrl = $fileBrowser['FileBrowser']['dir'] . $file;
						echo $this->Html->div('file-thumb', 
							$this->FileBrowser->thumbImage($file)
						);
						echo $this->Html->div('file-name',$file,array('title'=>$__fileUrl)); 
						echo $this->Html->tag('div',
							$this->Html->link(__("Rename"),
								//array('action'=>$this->params['action'],'cmd'=>'file_rename','dir'=>base64_encode($fileBrowser['dir']),'file'=>base64_encode($file)),
								$this->FileBrowser->url(array('cmd'=>'file_rename','file'=>$file)),
								array('class'=>'file-action file-rename','data-url'=>$__fileUrl)
							)
						);
						echo $this->Html->tag('div',
							$this->Html->link(__("Delete"),
								//array('action'=>$this->params['action'],'cmd'=>'file_delete','dir'=>base64_encode($fileBrowser['FileBrowser']['dir']),'file'=>base64_encode($file)),
								$this->FileBrowser->url(array('cmd'=>'file_delete','file'=>$file)),
								array('class'=>'file-action file-delete','data-url'=>$__fileUrl),
								__("Sure, that you want to delete the file '%s'",h($file))
							)
						);
						echo $this->Html->tag('div',
							$this->Html->link(__("Select"),'#',
								array('class'=>'file-action file-select','data-url'=>$__fileUrl)));
					?></div>
					<?php endforeach;?>
				</div>
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
	var filebrowserBaseUrl = '<?php echo @$fileBrowser['FileBrowser']['baseUrl']; ?>';

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
