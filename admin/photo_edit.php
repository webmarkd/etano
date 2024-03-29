<?php
/******************************************************************************
Etano
===============================================================================
File:                       admin/photo_edit.php
$Revision$
Software by:                DateMill (http://www.datemill.com)
Copyright by:               DateMill (http://www.datemill.com)
Support at:                 http://www.datemill.com/forum
*******************************************************************************
* See the "docs/licenses/etano.txt" file for license.                         *
******************************************************************************/

require_once '../includes/common.inc.php';
require_once '../includes/admin_functions.inc.php';
require_once '../includes/tables/user_photos.inc.php';
allow_dept(DEPT_ADMIN);

$tpl=new phemplate('skin/','remove_nonjs');

$output=$user_photos_default['defaults'];
if (isset($_SESSION['topass']['input'])) {
	$output=$_SESSION['topass']['input'];
	// our 'return' here was decoded in the processor
	$output['return2']=$output['return'];
	$output['return']=rawurlencode($output['return']);
} elseif (!empty($_GET['photo_id'])) {
	$photo_id=(int)$_GET['photo_id'];
	$query="SELECT *,UNIX_TIMESTAMP(`date_posted`) as `date_posted` FROM `{$dbtable_prefix}user_photos` WHERE `photo_id`=$photo_id";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	if (mysql_num_rows($res)) {
		$output=mysql_fetch_assoc($res);
		$output['caption']=sanitize_and_format($output['caption'],TYPE_STRING,$__field2format[TEXT_DB2EDIT]);
		$config=get_site_option(array('date_format'),'def_user_prefs');
		$output['date_posted']=strftime($config['date_format'],$output['date_posted']);
	}
}

$output['is_main']=($output['is_main']==1) ? 'checked="checked"' : '';
$output['is_private']=($output['is_private']==1) ? 'checked="checked"' : '';
$output['allow_rating']=($output['allow_rating']==1) ? 'checked="checked"' : '';
$output['allow_comments']=($output['allow_comments']==1) ? 'checked="checked"' : '';

if (empty($output['return'])) {
	// because of the GET, our 'return' is decoded
	$output['return2']=sanitize_and_format_gpc($_GET,'return',TYPE_STRING,$__field2format[FIELD_TEXTFIELD],'');
	$output['return']=rawurlencode($output['return2']);
}
$tpl->set_file('content','photo_edit.html');
$tpl->set_var('output',$output);
$tpl->process('content','content');

$tplvars['title']='Edit Photo';
$tplvars['page']='photo_edit';
$tplvars['css']='photo_edit.css';
include 'frame.php';
