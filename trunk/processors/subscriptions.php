<?php
/******************************************************************************
newdsb
===============================================================================
File:                       processors/subscriptions.php
$Revision: 56 $
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
check_login_member(3);

$error=false;
$qs='';
$qs_sep='';
$topass=array();
$nextpage='profile.php';
if ($_SERVER['REQUEST_METHOD']=='POST') {
	$input=array();
// get the input we need and sanitize it
	$input['subscr_id']=isset($_POST['subscr_id']) ? (int)$_POST['subscr_id'] : 0;
	$input['module_code']=sanitize_and_format_gpc($_POST,'module_code',TYPE_STRING,$__html2format[_HTML_TEXTFIELD_]);

	if (empty($input['subscr_id'])) {
		$error=true;
		$topass['message']['type']=MESSAGE_ERROR;
		$topass['message']['text']='Please select the desired membership type.';	//translate this
	}
	if (empty($input['module_code'])) {
		$error=true;
		$topass['message']['type']=MESSAGE_ERROR;
		$topass['message']['text']='Please select the desired payment system.';	//translate this
	}

	if (!$error) {
		$query="SELECT * FROM `{$dbtable_prefix}site_options3` WHERE b.`config_option`='module_active' AND b.`config_value`=1 AND a.`module_code`='".$input['module_code']."'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		$module=array();
		if (mysql_num_rows($res)) {
			$module=mysql_fetch_assoc($res);
		} else {
			$error=true;
			$topass['message']['type']=MESSAGE_ERROR;
			$topass['message']['text']='Invalid membership type. Please select another.';	//translate this
		}
		$query="SELECT * FROM `{$dbtable_prefix}subscriptions` WHERE `subscr_id`='".$input['subscr_id']."'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		$subscription=array();
		if (mysql_num_rows($res)) {
			$subscription=mysql_fetch_assoc($res);
			$subscription['user_id']=$_SESSION['user']['user_id'];
		} else {
			$error=true;
			$topass['message']['type']=MESSAGE_ERROR;
			$topass['message']['text']='Invalid membership type. Please select another.';	//translate this
		}

		if (!$error) {
			if (is_file(_BASEPATH_.'/plugins/payment/'.$input['module_code'].'/'.$input['module_code'].'.class.php')) {
				include_once _BASEPATH_.'/plugins/payment/'.$input['module_code'].'/'.$input['module_code'].'.class.php';
				$class='payment_'.$input['module_code'];

				$pay=new $class;
				$pay->redirect2gateway($subscription);
			}
		}
	}
}
redirect2page('folders.php',$topass,$qs);
?>