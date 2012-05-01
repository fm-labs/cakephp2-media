<?php $this->Helpers->load('Media.Uploadify');?>

<h4><?php echo __("Uploading to %s",$fileBrowser->Dir->Folder->pwd())?></h4>
<h4><?php echo __("Uploading to %s",$fileBrowser->Dir->path())?></h4>
<div class="upload_form">
	<input type="file" id="upload" name="upload" />
</div>

<script type="text/javascript">

$(document).ready(function() {
	$('#upload').uploadify({
		swf: '<?php echo Router::url('/');?>media/uploadify.swf',
		auto: true,
		checkExisting: '<?php echo Router::url(array('action'=>'check','plugin'=>'media'));?>',
		uploader: '<?php echo Router::url($options['uploaderUrl']);?>',
		cancelImage: '<?php echo Router::url('/');?>media/img/uploadify/uploadify-cancel.png',
		debug: true,
		fileSizeLimit   : 0,
		fileTypeDesc    : 'All Images',
		fileTypeExts    : '*.*',
		removeCompleted : false,
		removeTimeout   : 15,
		postData: <?php echo json_encode(array('dir'=> $fileBrowser->Dir->path())); ?>,
		onUploadSuccess: uploadSuccess,
		checkExisting: false,
	},{
		http_success : [200, 201, 202],
	});
});

function uploadSuccess(file,data,response) {
	console.log(response);
	console.log(file);
	console.log(data);
	data = jQuery.parseJSON(data);
	console.log(data);
	
	if (jQuery('#' + file.id)) {
		var target = jQuery('#'+file.id);
		//swfuploadify.queue.queueSize -= file.size;
		//delete swfuploadify.queue.files[file.id]
		//jQuery('#' + file.id).fadeOut(500,function() {
		//	jQuery(this).remove();
		//});
		target.find(".data").html("- "+data.status+": "+data.message);
	}

}


</script>