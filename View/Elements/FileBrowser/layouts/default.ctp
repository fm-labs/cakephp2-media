<?php $this->Html->css('/media/css/filebrowser',null,array('inline'=>false));?>
<?php $this->Html->script('/media/js/base64', array('inline'=>false)); ?>
<?php $this->Helpers->load('Media.FileTree');?>
<?php $this->FileTree->includeFileTree();?>

<?php $this->Helpers->load('Media.Uploadify');?>


<?php $this->Helpers->load('Media.FileBrowser');?>
<?php $this->FileBrowser->set($fileBrowser); ?>

<?php $directoryUrl = $this->FileBrowser->url(array('action'=>'browse','dir'=>null,'file'=>null));?>
<?php $connectorUrl = $this->FileBrowser->url(array('action'=>'connect','dir'=>null,'file'=>null));?>

<?php $previewUrl = $this->Html->url(array('action'=>'preview','plugin'=>'media'));?>
<?php $uploadUrl = $this->Html->url(array('action'=>'upload_form','plugin'=>'media'));?>

<div class="file_browser">
	<div class="grid_4">
		<div class="frame">
			<h2><?php echo __("Browse");?></h2>
			<div id="file_tree"></div>
		</div>
	</div>
	<div class="grid_8">
		<div id="folder_frame" class="frame"></div>
	</div>
	<div class="grid_4">
		<div id="file_frame" class="frame"></div>
	</div>
	<div class="clearfix"></div>
</div>


<div class="file_broser_debug">
	<?php debug($fileBrowser);?>
</div>

<script type="text/javascript">
var baseDir = '<?php echo $fileBrowser->Dir->path(); ?>';
var previewUrl = '<?php echo $previewUrl; ?>';

function previewFile(file) {
    console.log("File: "+file);

    var _previewurl = previewUrl + '/file:' + file;
    console.log("PreviewUrl: "+_previewurl);
    
    $.get(_previewurl, function(data) {
        $("#file_frame").html(data);
        /*
		$.colorbox({
			html:data,
			innerWidth: '80%',
			innerHeight: '80%'
		});
		*/
      })
      .error(function() { alert("error"); });
}

var directoryUrl = '<?php echo $directoryUrl; ?>';
var dirXhr;
function openDir(dir,deep) {
    var _directoryUrl = directoryUrl + '/dir:' + Base64.encode(dir);
    if (deep)
        _directoryUrl = _directoryUrl + '/deep:1';
        
    console.log("DirectoryUrl: "+_directoryUrl);
    
    $.get(_directoryUrl, function(data) {
        $("#folder_frame").html(data);
      })
      .error(function() { alert("error"); });
}

$(document).ready( function() {
    $('#file_tree').fileTree({
        root: baseDir,
        script: '<?php echo $connectorUrl; ?>',
        expandSpeed: 500,
        collapseSpeed: 500,
        multiFolder: false,
        onFolderExpand: openDir
    }, previewFile);
    openDir('/',true);
});
</script>