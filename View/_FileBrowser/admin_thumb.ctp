<?php $this->Helpers->load('Media.PhpThumb'); ?>

<h1><?php echo __("Admin Thumb");?></h1>

<?php echo $this->PhpThumb->image($path); ?>