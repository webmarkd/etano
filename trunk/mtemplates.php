<?php
/******************************************************************************
newdsb
===============================================================================
File:                       mtemplates.php
$Revision$
Software by:                DateMill (http://www.datemill.com)
Copyright by:               DateMill (http://www.datemill.com)
Support at:                 http://forum.datemill.com
*******************************************************************************
* See the "softwarelicense.txt" file for license.                             *
******************************************************************************/

require_once 'includes/sessions.inc.php';
require_once 'includes/vars.inc.php';
db_connect(_DBHOSTNAME_,_DBUSERNAME_,_DBPASSWORD_,_DBNAME_);
require_once 'includes/classes/phemplate.class.php';
require_once 'includes/user_functions.inc.php';
require_once 'includes/tables/user_mtpls.inc.php';
check_login_member(4);

$tpl=new phemplate($tplvars['tplrelpath'].'/','remove_nonjs');

$o=isset($_GET['o']) ? (int)$_GET['o'] : 0;
$r=(isset($_GET['r']) && !empty($_GET['r'])) ? (int)$_GET['r'] : current($accepted_results_per_page);

$from="`{$dbtable_prefix}user_mtpls`";
$where="`fk_user_id`='".$_SESSION['user']['user_id']."'";

$query="SELECT count(*) FROM $from WHERE $where";
if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
$totalrows=mysql_result($res,0,0);

$loop=array();
if (!empty($totalrows)) {
	$query="SELECT `mtpl_id`,`subject`,`message_body` FROM $from WHERE $where LIMIT $o,$r";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	while ($rsrow=mysql_fetch_assoc($res)) {
		$rsrow['subject']=sanitize_and_format($rsrow['subject'],TYPE_STRING,$__field2format[TEXT_DB2DISPLAY]);
		$rsrow['message_body']=text2smilies(bbcode2html(sanitize_and_format($rsrow['message_body'],TYPE_STRING,$__field2format[TEXT_DB2DISPLAY])));
		$loop[]=$rsrow;
	}

	$output['pager2']=pager($totalrows,$o,$r);
}

$output['return2me']='mtemplates.php';
if (!empty($_SERVER['QUERY_STRING'])) {
	$output['return2me'].='?'.$_SERVER['QUERY_STRING'];
}
$output['return2me']=rawurlencode($output['return2me']);
$tpl->set_file('content','mtemplates.html');
$tpl->set_var('output',$output);
$tpl->set_loop('loop',$loop);
$tpl->process('content','content',TPL_LOOP | TPL_NOLOOP);
$tpl->drop_loop('loop');
unset($loop);

$tplvars['title']='Manage your message templates';     // translate
$tplvars['page_title']='Message templates';
$tplvars['page']='mtemplates';
$tplvars['css']='mtemplates.css';
if (is_file('mtemplates_left.php')) {
	include 'mtemplates_left.php';
}
include 'frame.php';
?>