<?php 
global $AJXP_GLUE_GLOBALS;
$AJXP_GLUE_GLOBALS = array();
$AJXP_GLUE_GLOBALS["secret"] = 'myprivatesecret1';
$AJXP_GLUE_GLOBALS["plugInAction"] = "login";
$AJXP_GLUE_GLOBALS["login"] = "login";
$AJXP_GLUE_GLOBALS["password"] = "login";
$AJXP_GLUE_GLOBALS["checkPassord"] = false;
include($ajaxplorerRootPath.'plugins'.DS.'auth.remote'.DS.'glueCode.php');
?>