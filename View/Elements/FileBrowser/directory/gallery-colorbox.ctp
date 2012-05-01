<?php $this->Helpers->load('Media.PhpThumb'); ?>
<?php $directory =& $fileBrowser->Dir; ?>
<div class="gallery">

	<ul class="gallery">
	<?php 
		$_dir64 = base64_encode($directory->path());
		$contents = $directory->Folder->read(true,array('.','.svn'));
		foreach ($contents[1] as $file):
		
			$fileBrowser->Resource = new LibFileBrowserFile($fileBrowser->Dir, $file, false);
			
			if (!$fileBrowser->Resource->isImage())
				continue;
			
			$_file = $directory->path().$file;
			$_file64 = base64_encode($_file);
			$image = $this->PhpThumb->image($fileBrowser->Resource->File->pwd(),array('w'=>120,'h'=>100,'q'=>80,'iar'=>1));
			$imageUrl = $this->PhpThumb->url($fileBrowser->Resource->File->pwd(),array('w'=>700,'h'=>500,'q'=>100,'iar'=>0));
			$link = $this->Html->link($image, $imageUrl,array('escape'=>false,'rel'=>'gal','class'=>'gallery'));
			$html = $this->Html->tag('span',$link,array('thumb'));
			$html .= $this->Html->tag('span',$file,array('name'));
			/*
			$html .= $this->Html->tag('span',
				$this->Html->link($file,array('cmd'=>'view','dir'=>$_dir64,'file'=>$_file64),array('data-file'=>$_file)),
				array('class' => 'file_name')
			);
			*/
			
			$attr = array('class'=>'gallery');
			echo $this->Html->tag('li',$html,$attr); 
		endforeach;
	?>
	</ul>


</div>
<script type="text/javascript">
$(document).ready(function() {
	$('a.gallery').colorbox({rel:'gal'});	
});

</script>