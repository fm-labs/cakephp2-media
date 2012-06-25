<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo __d('media',"ImageBrowser"); ?> |
		<?php echo $title_for_layout; ?>
	</title>
	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css('/media/css/reset');
		echo $this->Html->css('/admin_panel/css/screen/cake');
		echo $this->Html->css('/admin_panel/css/screen/style');
		echo $this->Html->css('/media/css/filebrowser');

		$this->Js->loadJquery();
		echo $this->Html->css('/jquery/css/ui/smoothness/jquery-ui-1.8.14.custom');
	
		echo $this->fetch('script');
		echo $this->Html->script('/media/js/jquery.scrollTo/jquery.scrollTo-min');
		echo $this->fetch('css');
		
	?>
	
</head>
<body>
	<div id="container">
		
		<div id="contentwrap">
			<div id="contentcontainer" class="container_16">
				<div id="flash" class="canhide">
					<?php echo $this->Session->flash(); ?>
					<?php echo $this->Session->flash('auth'); ?>
				</div>
				<div class="clearfix"></div>
				<div id="content">
					<?php echo $this->fetch('content'); ?>
					<div class="clearfix"></div>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
	</div>
	
<?php if (isset($this->Js)) echo $this->Js->writeBuffer(); ?>
</body>
</html>