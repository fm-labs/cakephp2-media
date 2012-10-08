<?php $this->Helpers->load('Media.FileBrowser');?>
<?php $this->FileBrowser->setFileBrowser($fileBrowser);?>
<?php $this->Js->loadPlugin('JqueryColorbox.JqueryColorbox'); ?>
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
		<div class="file-browser-column">
		<!-- COL 1 -->
		<div class="file-browser-col1">
		<div class="inner">
			<h2><?php echo __d('media',"Current Folder: %s",'/'.$fileBrowser['FileBrowser']['dir']);?>&nbsp;
			<?php echo $this->Html->link(__d('media',"Reload"),$this->Html->url(null,true));?>
			</h2>
			<div>
				<div class="file-browser-list">
					<!-- DIRECTORIES -->
					<table class="file-browser-list">
						<tr>
							<th class="type">&nbsp;</th>
							<th class="name"><?php echo __('Name'); ?></th>
							<th class="actions"><?php echo __('Actions'); ?></th>
						</tr>
						<!-- CURRENT DIR -->
						<tr class="folder current">
							<td class="type">&nbsp;</td>
							<td class="name"><?php 
								echo $this->Html->link('.',
									$this->Html->url(null,true)); 
							?></td>
							<td>&nbsp;</td>
						</tr>
						<!-- PARENT DIR -->
						<tr class="folder parent">
							<td class="type">&nbsp;</td>
							<td class="name"><?php 
								echo $this->Html->link('..', $this->FileBrowser->url(array('action'=>'parent')));?></td>
							<td>&nbsp;</td>
						</tr>
						<?php foreach($fileBrowser['Folder'] as $folder):?>
						<tr class="folder">
							<td class="type">&nbsp;</td>
							<td class="name"><?php 
									echo $this->Html->link($folder, $this->FileBrowser->dirUrl(array('action'=>'open'),$folder));
							?>
							</td>
							<td class="actions">
								<ul class="actions">
									<li><?php echo $this->Html->link(__('Open'),$this->FileBrowser->dirUrl(array('action'=>'open'),$folder));?></li>
								</ul>
							</td>
						</tr>
						<?php endforeach;?>
					
					<!-- FILES -->
						<?php foreach($fileBrowser['File'] as $file):?>
						<tr class="file" id="<?php echo md5($file); ?>">
							<td class="type">&nbsp;</td>
							<td class="name"><?php echo $file; ?></td>
							<td class="actions">
								<ul class="actions">
									<li><?php echo $this->Html->link(__d('media',"View"),
										$this->FileBrowser->fileUrl(array('action'=>'view'),$file),
										array()
									); ?></li>
									<li><?php echo $this->Html->link(__d('media',"Rename"),
										$this->FileBrowser->fileUrl(array('action'=>'rename'),$file),
										array()
									); ?></li>
									<li><?php echo $this->Html->link(__d('media',"Copy"),
										$this->FileBrowser->fileUrl(array('action'=>'copy'),$file),
										array()
									); ?></li>
									<li><?php echo $this->Html->link(__d('media',"Delete"),
										$this->FileBrowser->fileUrl(array('action'=>'delete'),$file),
										array(),
										__d('media',"Sure, that you want to delete the file '%s'",h($file))
									); ?></li>
									<li><?php echo $this->Html->link(__d('media',"Select"),'#',
										array()); ?>
								</ul>
							</td>
						</tr>
						<?php endforeach; ?>
					</table>
				</div>
				
				<dl>
					<dt><?php echo __('Config')?></dt>
					<dd><?php echo $fileBrowser['FileBrowser']['config']; ?>&nbsp;</dd>
					<dt><?php echo __('BasePath')?></dt>
					<dd><?php echo $fileBrowser['FileBrowser']['basePath']; ?>&nbsp;</dd>
					<dt><?php echo __('Dir')?></dt>
					<dd><?php echo $fileBrowser['FileBrowser']['dir']; ?>&nbsp;</dd>
					<dt><?php echo __('File')?></dt>
					<dd><?php echo $fileBrowser['FileBrowser']['file']; ?>&nbsp;</dd>
					<dt><?php echo __('Folders')?></dt>
					<dd><?php echo count($fileBrowser['Folder']); ?>&nbsp;</dd>
					<dt><?php echo __('Files')?></dt>
					<dd><?php echo count($fileBrowser['File']); ?>&nbsp;</dd>
				</dl>
				
				
				<div id="file-browser-debug">
				<?php debug($fileBrowser);?>
				<?php debug($this->Session->read('Media.FileBrowser')); ?>
				</div>
			</div>
		</div>
		</div>
		<!-- #COL1 -->
		
		
		<!-- COL2 -->
		<div class="file-browser-col2">
		<div class="inner">
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
					<?php echo $this->Html->link(__d('media',"Large Preview"),'#',array('class'=>'file-browser-btn-preview-large'));?>
					<?php #echo $this->Html->link(__d('media',"Source"),'#',array('class'=>'file-browser-btn-source'));?>
					<?php #echo $this->Html->link(__d('media',"Download"),'#',array('class'=>'file-browser-btn-download'));?>
					<?php else :?>
					<?php echo $this->Html->image('/media/img/filebrowser/_default.jpg', array(
						'id' => 'file-browser-preview-image',
						'alt' => 'Preview',
					));?>
					<?php endif; ?>
				</div>
				<?php 
				$this->Js->get('.__file-browser-btn-preview-large')->plugin('JqueryColorbox')->colorbox(array(
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
					<h4><?php echo __d('media',"Upload to %s", h($uploadDir));?></h4>
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
		</div>
		<!--  #COL2 -->
		<div class="clearfix"></div>
		
		
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
