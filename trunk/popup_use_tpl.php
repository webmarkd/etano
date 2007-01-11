<?php
/******************************************************************************
newdsb
===============================================================================
File:                       popup_use_tpl.php
$Revision: 85 $
Software by:                DateMill (http://www.datemill.com)
Copyright by:               DateMill (http://www.datemill.com)
Support at:                 http://forum.datemill.com
*******************************************************************************
* See the "softwarelicense.txt" file for license.                             *
******************************************************************************/

require_once 'includes/sessions.inc.php';
require_once 'includes/classes/phemplate.class.php';
require_once 'includes/user_functions.inc.php';
require_once 'includes/vars.inc.php';
db_connect(_DBHOSTNAME_,_DBUSERNAME_,_DBPASSWORD_,_DBNAME_);
check_login_member(15);

$tpl=new phemplate(_BASEPATH_.'/skins/'.get_my_skin().'/','remove_nonjs');

$templates=array();
$query="SELECT `mtpl_id`,`subject`,`message_body` FROM `{$dbtable_prefix}user_mtpls` WHERE `fk_user_id`='".$_SESSION['user']['user_id']."'";
if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
while ($rsrow=mysql_fetch_assoc($res)) {
	$templates[]=$rsrow;
}
$templates=sanitize_and_format($templates,TYPE_STRING,$__html2format[TEXT_DB2EDIT]);

$tpl->set_file('content','popup_use_tpl.html');
$tpl->set_loop('templates',$templates);
$tpl->set_var('tplvars',$tplvars);
echo $tpl->process('','content',TPL_FINISH | TPL_LOOP);
?>