<?php
/******************************************************************************
Etano
===============================================================================
File:                       processors/filters_addedit.php
$Revision$
Software by:                DateMill (http://www.datemill.com)
Copyright by:               DateMill (http://www.datemill.com)
Support at:                 http://www.datemill.com/forum
*******************************************************************************
* See the "docs/licenses/etano.txt" file for license.                         *
******************************************************************************/

require_once '../includes/common.inc.php';
db_connect(_DBHOST_,_DBUSER_,_DBPASS_,_DBNAME_);
require_once '../includes/user_functions.inc.php';
require_once '../includes/tables/message_filters.inc.php';
check_login_member('manage_folders');

if (is_file(_BASEPATH_.'/events/processors/filters_addedit.php')) {
	include_once _BASEPATH_.'/events/processors/filters_addedit.php';
}

$error=false;
$qs='';
$qs_sep='';
$topass=array();
$nextpage='filters.php';
$input=array();
if ($_SERVER['REQUEST_METHOD']=='POST') {
// get the input we need and sanitize it
	foreach ($message_filters_default['types'] as $k=>$v) {
		$input[$k]=sanitize_and_format_gpc($_POST,$k,$__field2type[$v],$__field2format[$v],$message_filters_default['defaults'][$k]);
	}
	$input['fk_user_id']=$_SESSION['user']['user_id'];

	switch ($input['filter_type']) {
		case FILTER_SENDER:
			if (!($input['field_value']=get_userid_by_user($input['field_value']))) {
				$error=true;
				$topass['message']['type']=MESSAGE_ERROR;
				$topass['message']['text']=sprintf('User %s doesn\'t exist. Filter not saved.',$input['field_value']);     // translate
			}
			break;

		case FILTER_SENDER_PROFILE:
		case FILTER_MESSAGE:
		default:
			break;

	}

} else {
// not working
	$input['filter_id']=$message_filters_default['defaults']['filter_id'];
	$input['filter_type']=_FILTER_USER_;
	$input['fk_user_id']=$_SESSION['user']['user_id'];
	$input['field']='fk_user_id';
	$input['field_value']=isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
	$input['fk_folder_id']=FOLDER_SPAMBOX;
	$nextpage='message_read.php';
}

if (!$error) {
	if (!empty($input['filter_id'])) {
		$query="UPDATE IGNORE `{$dbtable_prefix}message_filters` SET ";
		foreach ($message_filters_default['defaults'] as $k=>$v) {
			if (isset($input[$k])) {
				$query.="`$k`='".$input[$k]."',";
			}
		}
		$query=substr($query,0,-1);
		$query.=" WHERE `filter_id`=".$input['filter_id']." AND `fk_user_id`='".$_SESSION['user']['user_id']."'";
		if (isset($_on_before_update)) {
			for ($i=0;isset($_on_before_update[$i]);++$i) {
				call_user_func($_on_before_update[$i]);
			}
		}
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		if (mysql_affected_rows()) {
			$topass['message']['type']=MESSAGE_INFO;
			$topass['message']['text']='Filter changed successfully.';     // translate
		} else {
			$topass['message']['type']=MESSAGE_ERROR;
			$topass['message']['text']='Filter not changed. A similar filter already exists.';     // translate
		}
		if (isset($_on_after_update)) {
			for ($i=0;isset($_on_after_update[$i]);++$i) {
				call_user_func($_on_after_update[$i]);
			}
		}
	} else {
		unset($input['filter_id']);
		$query="INSERT IGNORE INTO `{$dbtable_prefix}message_filters` SET ";
		foreach ($message_filters_default['defaults'] as $k=>$v) {
			if (isset($input[$k])) {
				$query.="`$k`='".$input[$k]."',";
			}
		}
		$query=substr($query,0,-1);
		if (isset($_on_before_insert)) {
			for ($i=0;isset($_on_before_insert[$i]);++$i) {
				call_user_func($_on_before_insert[$i]);
			}
		}
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		if (mysql_affected_rows()) {
			$topass['message']['type']=MESSAGE_INFO;
			$topass['message']['text']='Filter added.';     // translate
		} else {
			$topass['message']['type']=MESSAGE_ERROR;
			$topass['message']['text']='Filter not added. A similar filter already exists.';     // translate
		}
		if (isset($_on_after_insert)) {
			for ($i=0;isset($_on_after_insert[$i]);++$i) {
				call_user_func($_on_after_insert[$i]);
			}
		}
	}
} else {
	$nextpage='filters_addedit.php';
// 		you must re-read all textareas from $_POST like this:
//		$input['x']=addslashes_mq($_POST['x']);
	$input=sanitize_and_format($input,TYPE_STRING,FORMAT_HTML2TEXT_FULL | FORMAT_STRIPSLASH);
	$topass['input']=$input;
	if (isset($_on_error)) {
		for ($i=0;isset($_on_error[$i]);++$i) {
			call_user_func($_on_error[$i]);
		}
	}
}

if (isset($_GET['mail_id'])) {
	$qs.=$qs_sep.'mail_id='.$_GET['mail_id'];
	$qs_sep='&';
}
if (isset($_GET['fid'])) {
	$qs.=$qs_sep.'fid='.$_GET['fid'];
	$qs_sep='&';
}
if (isset($_POST['o'])) {
	$qs.=$qs_sep.'o='.$_POST['o'];
	$qs_sep='&';
}
if (isset($_POST['r'])) {
	$qs.=$qs_sep.'r='.$_POST['r'];
	$qs_sep='&';
}
if (isset($_POST['ob'])) {
	$qs.=$qs_sep.'ob='.$_POST['ob'];
	$qs_sep='&';
}
if (isset($_POST['od'])) {
	$qs.=$qs_sep.'od='.$_POST['od'];
	$qs_sep='&';
}
redirect2page($nextpage,$topass,$qs);
