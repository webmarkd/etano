<?php
/******************************************************************************
newdsb
===============================================================================
File:                       admin/processors/photo_approve.php
$Revision$
Software by:                DateMill (http://www.datemill.com)
Copyright by:               DateMill (http://www.datemill.com)
Support at:                 http://forum.datemill.com
*******************************************************************************
* See the "softwarelicense.txt" file for license.                             *
******************************************************************************/

require_once '../../includes/sessions.inc.php';
require_once '../../includes/vars.inc.php';
db_connect(_DBHOSTNAME_,_DBUSERNAME_,_DBPASSWORD_,_DBNAME_);
require_once '../../includes/classes/phemplate.class.php';
require_once '../../includes/admin_functions.inc.php';
allow_dept(DEPT_MODERATOR | DEPT_ADMIN);

$error=false;
$qs='';
$qs_sep='';
$topass=array();
$input=array();
if (isset($_GET['photo_id']) && !empty($_GET['photo_id'])) {
	$input['photo_id']=(int)$_GET['photo_id'];
	$input['return']=sanitize_and_format_gpc($_GET,'return',TYPE_STRING,$__field2format[FIELD_TEXTFIELD] | FORMAT_RUDECODE,'');

	$query="UPDATE `{$dbtable_prefix}user_photos` SET `status`='".STAT_APPROVED."',`reject_reason`='',`last_changed`='".gmdate('YmdHis')."' WHERE `photo_id`='".$input['photo_id']."'";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

	// make this photo the main photo if it is_main
	$query="SELECT `is_main`,`fk_user_id`,`photo` FROM `{$dbtable_prefix}user_photos` WHERE `photo_id`='".$input['photo_id']."'";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	if (mysql_num_rows($res)) {
		$rsrow=mysql_fetch_assoc($res);
		if (!empty($rsrow['is_main'])) {
			$query="UPDATE `{$dbtable_prefix}user_profiles` SET `_photo`='".$rsrow['photo']."' WHERE `fk_user_id`='".$rsrow['fk_user_id']."'";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		}
	}
	$topass['message']['type']=MESSAGE_INFO;
	$topass['message']['text']='Photo approved.';
}

$nextpage=_BASEURL_.'/admin/photo_search.php';
if (isset($input['return'])) {
	$nextpage=_BASEURL_.'/admin/'.$input['return'];
}
redirect2page($nextpage,$topass,'',true);
?>