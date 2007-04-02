<?php
/******************************************************************************
newdsb
===============================================================================
File:                       processors/search_delete.php
$Revision: 67 $
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
check_login_member(14);

$topass=array();
$search_id=isset($_GET['sid']) ? (int)$_GET['sid'] : 0;

$query="DELETE FROM `{$dbtable_prefix}user_searches` WHERE `search_id`='$search_id' AND `fk_user_id`='".$_SESSION['user']['user_id']."'";
if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

$topass['message']['type']=MESSAGE_INFO;
$topass['message']['text']='Your search has been deleted';     // translate

$nextpage='my_searches.php';
if (isset($_POST['return']) && !empty($_POST['return'])) {
	$input['return']=rawurldecode(sanitize_and_format_gpc($_POST,'return',TYPE_STRING,$__html2format[HTML_TEXTFIELD],''));
	$nextpage=$input['return'];
}
$nextpage=_BASEURL_.'/'.$nextpage;
redirect2page($nextpage,$topass,'',true);
?>