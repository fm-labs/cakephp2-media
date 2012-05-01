<?php 
/*
<ul class="jqueryFileTree" style="display: none;">
    <li class="directory collapsed"><a href="#" rel="/this/folder/">Folder Name</a></li>
    <li class="file ext_txt"><a href="#" rel="/this/folder/filename.txt">filename.txt</a></li>
</ul>
*/
$this->Helpers->load('Media.FileTree');
echo $this->FileTree->formatOutput($contents, $dir);
?>