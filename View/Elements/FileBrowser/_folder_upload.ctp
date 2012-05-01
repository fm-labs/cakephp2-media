<?php $this->Helpers->load('Media.Uploadify',array('loadJquery' => true));?>
<div class="upload_form">
	<input type="file" id="upload" name="upload" />
</div>


<script type="text/javascript">

$(document).ready(function() {
	$('#upload').uploadify({
		swf: '<?php echo Router::url('/');?>media/uploadify.swf',
		auto: true,
		checkExisting: '<?php echo Router::url(array('action'=>'check','plugin'=>'media'));?>',
		uploader: '<?php echo Router::url(array('action'=>'upload','plugin'=>'media','dir'=>base64_encode($baseDir)));?>',
		cancelImage: '<?php echo Router::url('/');?>media/img/uploadify/uploadify-cancel.png',
		debug: true,
		fileSizeLimit   : 0,
		fileTypeDesc    : 'All Images',
		fileTypeExts    : '*.*',
		removeCompleted : false,
		removeTimeout   : 15,
		postData: <?php echo json_encode(array('dir'=> $baseDir)); ?>,
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

	if (data.status == "SUCCESS") {
		openDir(data.uploadDir);
	}
}


</script>