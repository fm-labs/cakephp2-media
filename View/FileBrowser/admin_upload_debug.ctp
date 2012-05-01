<h1>CAKE REQUEST</h1>

<pre class="cake-debug">
object(CakeRequest) {
	params =&gt; array(
		&#039;plugin&#039; =&gt; &#039;media&#039;,
		&#039;controller&#039; =&gt; &#039;file_upload&#039;,
		&#039;action&#039; =&gt; &#039;admin_upload&#039;,
		&#039;form&#039; =&gt; array(
			&#039;Filedata&#039; =&gt; array(
				&#039;name&#039; =&gt; &#039;2011-10-06 Musterauswertung MK UM.pdf&#039;,
				&#039;type&#039; =&gt; &#039;application/octet-stream&#039;,
				&#039;tmp_name&#039; =&gt; &#039;D:\xampp\tmp\phpDAEA.tmp&#039;,
				&#039;error&#039; =&gt; (int) 0,
				&#039;size&#039; =&gt; (int) 1011245
			)
		),
		&#039;named&#039; =&gt; array(),
		&#039;pass&#039; =&gt; array(),
		&#039;prefix&#039; =&gt; &#039;*****&#039;,
		&#039;admin&#039; =&gt; true
	)
	data =&gt; array(
		&#039;Filename&#039; =&gt; &#039;2011-10-06 Musterauswertung MK UM.pdf&#039;,
		&#039;root&#039; =&gt; &#039;/&#039;,
		&#039;basepath&#039; =&gt; &#039;__default__&#039;,
		&#039;Upload&#039; =&gt; &#039;Submit Query&#039;
	)
	query =&gt; array()
	url =&gt; &#039;admin/media/file_upload/upload&#039;
	base =&gt; &#039;/site.drumhouse.at&#039;
	webroot =&gt; &#039;/site.drumhouse.at/&#039;
	here =&gt; &#039;/site.drumhouse.at/admin/media/file_upload/upload&#039;
}
</pre>
<br /><br />
<h1>FILES</h1>

<div>
<span><strong>app\Plugin\Media\Controller\Component\UploadifyComponent.php</strong> (line <strong>35</strong>)</span>
<pre class="cake-debug">
array(
	&#039;/admin/media/file_upload/upload&#039; =&gt; &#039;&#039;,
	&#039;Filename&#039; =&gt; &#039;2011-10-06 Musterauswertung MK UM.pdf&#039;,
	&#039;root&#039; =&gt; &#039;/&#039;,
	&#039;basepath&#039; =&gt; &#039;__default__&#039;,
	&#039;Upload&#039; =&gt; &#039;Submit Query&#039;,
	&#039;DRUMHOUSE-SITE&#039; =&gt; &#039;kg1d9dcchuprdr04i1ur2qdqg6&#039;
)
</pre>
</div><div class="cake-debug-output">
<span><strong>app\Plugin\Media\Controller\Component\UploadifyComponent.php</strong> (line <strong>36</strong>)</span>
<pre class="cake-debug">
array(
	&#039;Filedata&#039; =&gt; array(
		&#039;name&#039; =&gt; &#039;2011-10-06 Musterauswertung MK UM.pdf&#039;,
		&#039;type&#039; =&gt; &#039;application/octet-stream&#039;,
		&#039;tmp_name&#039; =&gt; &#039;D:\xampp\tmp\php52B9.tmp&#039;,
		&#039;error&#039; =&gt; (int) 0,
		&#039;size&#039; =&gt; (int) 1011245
	)
)
</pre>
</div>

<h2>Nested Post Data</h2>
<div>
<pre class="cake-debug">
array(
	&#039;/admin/media/file_upload/upload&#039; =&gt; &#039;&#039;,
	&#039;Filename&#039; =&gt; &#039;2011-10-06 Musterauswertung MK MSM.pdf&#039;,
	&#039;Upload&#039; =&gt; &#039;Submit Query&#039;,
	&#039;DRUMHOUSE-SITE&#039; =&gt; &#039;kg1d9dcchuprdr04i1ur2qdqg6&#039;
)
</pre>
</div><div class="cake-debug-output">
<span><strong>app\Plugin\Media\Controller\Component\UploadifyComponent.php</strong> (line <strong>36</strong>)</span>
<pre class="cake-debug">
array(
	&#039;Filedata&#039; =&gt; array(
		&#039;name&#039; =&gt; &#039;2011-10-06 Musterauswertung MK MSM.pdf&#039;,
		&#039;type&#039; =&gt; &#039;application/octet-stream&#039;,
		&#039;tmp_name&#039; =&gt; &#039;D:\xampp\tmp\php69F2.tmp&#039;,
		&#039;error&#039; =&gt; (int) 0,
		&#039;size&#039; =&gt; (int) 1023588
	)
)
</pre>
</div>