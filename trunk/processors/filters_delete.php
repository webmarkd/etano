<?php
/******************************************************************************
newdsb
===============================================================================
File:                       processors/filters_delete.php
$Revision: 0 $
Software by:                DateMill (http://www.datemill.com)
Copyright by:               DateMill (http://www.datemill.com)
Support at:                 http://forum.datemill.com
*******************************************************************************
* See the "softwarelicense.txt" file for license.                             *
******************************************************************************/

require_once '../includes/sessions.inc.php';
require_once '../includes/classes/phemplate.class.php';
require_once '../includes/user_functions.inc.php';
require_once '../includes/vars.inc.php';
db_connect(_DBHOSTNAME_,_DBUSERNAME_,_DBPASSWORD_,_DBNAME_);
check_login_member(5);

$qs='';
$qs_sep='';
$topass=array();
$filter_id=isset($_GET['filter_id']) ? (int)$_GET['filter_id'] : 0;

$query="DELETE FROM `{$dbtable_prefix}message_filters` WHERE `filter_id`='$filter_id' AND `fk_user_id`='".$_SESSION['user']['user_id']."'";
if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

$topass['message']['type']=MESSAGE_INFO;
$topass['message']['text']='Filter deleted.';     // translate

if (isset($_GET['o'])) {
	$qs.=$qs_sep.'o='.$_GET['o'];
	$qs_sep='&';
}
if (isset($_GET['r'])) {
	$qs.=$qs_sep.'r='.$_GET['r'];
	$qs_sep='&';
}
if (isset($_GET['ob'])) {
	$qs.=$qs_sep.'ob='.$_GET['ob'];
	$qs_sep='&';
}
if (isset($_GET['od'])) {
	$qs.=$qs_sep.'od='.$_GET['od'];
	$qs_sep='&';
}

redirect2page('filters.php',$topass,$qs);
?>