<?php
/******************************************************************************
Etano
===============================================================================
File:                       processors/blog_comment_delete.php
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
require_once '../includes/triggers.inc.php';
check_login_member('auth');

if (is_file(_BASEPATH_.'/events/processors/blog_comment_delete.php')) {
	include_once _BASEPATH_.'/events/processors/blog_comment_delete.php';
}

$topass=array();
$comment_id=isset($_GET['comment_id']) ? (int)$_GET['comment_id'] : 0;

if (!empty($comment_id)) {
	$query="SELECT b.`fk_user_id` FROM `{$dbtable_prefix}blog_comments` a,`{$dbtable_prefix}blog_posts` b WHERE a.`comment_id`=$comment_id AND a.`fk_parent_id`=b.`post_id`";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	if (mysql_num_rows($res) && mysql_result($res,0,0)==$_SESSION['user']['user_id']) {
		// delete only if I am the owner of the original post this comment's been made on
		$query="DELETE FROM `{$dbtable_prefix}blog_comments` WHERE `comment_id`=$comment_id";
		if (isset($_on_before_delete)) {
			for ($i=0;isset($_on_before_delete[$i]);++$i) {
				call_user_func($_on_before_delete[$i]);
			}
		}

		on_before_delete_comment(array($comment_id),'blog');

		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

		$topass['message']['type']=MESSAGE_INFO;
		$topass['message']['text']='Comment deleted.';     // translate
		if (isset($_on_after_delete)) {
			for ($i=0;isset($_on_after_delete[$i]);++$i) {
				call_user_func($_on_after_delete[$i]);
			}
		}
	} else {
		$topass['message']['type']=MESSAGE_ERROR;
		$topass['message']['text']='You are not allowed to delete this comment.';     // translate
	}
}

if (!empty($_GET['return'])) {
	$input['return']=sanitize_and_format_gpc($_GET,'return',TYPE_STRING,$__field2format[FIELD_TEXTFIELD],'');
	$nextpage=$input['return'];
} else {
	$nextpage='home.php';
}
$nextpage=_BASEURL_.'/'.$nextpage;
redirect2page($nextpage,$topass,'',true);
