<?php $this->Html->script('/media/js/upload',array('inline'=>false)); ?>
<div class="form">

<h2>I want to upload a file, please!</h2>
<br />
Okay, pick a file then...

<button id="selectFileButton">Select File</button>
<br /><br />
.. or use the upload form
<form enctype="multipart/form-data" method="post" action="<?php echo Router::url(array('action'=>'test')); ?>">
	<input type="file" id="selectFileForm" />
</form>

</div>
<script>
var myuploader = new fUploader();

var file = {};
myuploader.queue.add(file);

</script>