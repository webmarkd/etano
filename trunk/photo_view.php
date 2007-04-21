<?php
/******************************************************************************
newdsb
===============================================================================
File:                       photo_view.php
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
check_login_member(13);

$tpl=new phemplate($tplvars['tplrelpath'].'/','remove_nonjs');
$photo_id=sanitize_and_format_gpc($_GET,'photo_id',TYPE_INT,0,0);
$edit_comment=sanitize_and_format_gpc($_GET,'edit_comment',TYPE_INT,0,0);

$output=array();
$output['pic_width']=get_site_option('pic_width','core_photo');

$loop=array();
if (!empty($photo_id)) {
	$query="SELECT `photo_id`,`photo`,`caption`,`fk_user_id`,`_user` as `user`,`allow_comments`,`allow_rating`,`stat_votes`,`stat_votes_total` FROM `{$dbtable_prefix}user_photos` WHERE `photo_id`='$photo_id' AND `status`='".STAT_APPROVED."'";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	if (mysql_num_rows($res)) {
		$output=array_merge($output,mysql_fetch_assoc($res));
		$output['caption']=sanitize_and_format($output['caption'],TYPE_STRING,$__html2format[TEXT_DB2DISPLAY]);
		if (!empty($output['allow_rating'])) {
			if ($output['stat_votes']>0) {
				$output['rate_num']=number_format($output['stat_votes_total']/$output['stat_votes'],1);
			} else {
				$output['rate_num']=0;
			}
			$output['rate_percent']=(int)(($output['rate_num']*100)/5);
		} else {
			unset($output['allow_rating']);
		}
		if (isset($_SESSION['user']['user_id']) && $output['fk_user_id']==$_SESSION['user']['user_id']) {
			$output['photo_owner']=true;
		}

		if (!empty($output['allow_comments'])) {
			// may I see any comment?
			$output['show_comments']=true;
			$config=get_site_option(array('use_captcha','bbcode_comments'),'core');
			$query="SELECT a.`comment_id`,a.`comment`,a.`fk_user_id`,a.`_user` as `user`,b.`_photo` as `photo` FROM `{$dbtable_prefix}photo_comments` a LEFT JOIN `{$dbtable_prefix}user_profiles` b ON a.`fk_user_id`=b.`fk_user_id` WHERE a.`fk_photo_id`='".$output['photo_id']."' AND a.`status`=".STAT_APPROVED." ORDER BY a.`date_posted` ASC";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
			while ($rsrow=mysql_fetch_assoc($res)) {
				// if someone has asked to edit his/her comment
				if ($edit_comment==$rsrow['comment_id']) {
					$output['comment_id']=$rsrow['comment_id'];
					$output['comment']=sanitize_and_format($rsrow['comment'],TYPE_STRING,$__html2format[TEXT_DB2EDIT]);
				}
				$rsrow['comment']=sanitize_and_format($rsrow['comment'],TYPE_STRING,$__html2format[TEXT_DB2DISPLAY]);
				if (!empty($config['bbcode_comments'])) {
					$rsrow['comment']=bbcode2html($rsrow['comment']);
				}
				// allow showing the edit links to rightfull owners
				if (isset($_SESSION['user']['user_id']) && $rsrow['fk_user_id']==$_SESSION['user']['user_id']) {
					$rsrow['editme']=true;
				}

				if (empty($rsrow['fk_user_id'])) {	// for the link to member profile
					unset($rsrow['fk_user_id']);
				}
				if (empty($rsrow['photo']) || !is_file(_PHOTOPATH_.'/t1/'.$rsrow['photo'])) {
					$rsrow['photo']='no_photo.gif';
				}
				$loop[]=$rsrow;
			}

			// may I post comments please?
			if (allow_at_level(9,$_SESSION['user']['membership'])) {
				if (!isset($_SESSION['user']['user_id'])) {
					if ($config['use_captcha']) {
						require_once 'includes/classes/sco_captcha.class.php';
						$c=new sco_captcha(_BASEPATH_.'/includes/fonts',4);
						$_SESSION['captcha_word']=$c->gen_rnd_string(4);
						$output['rand']=make_seed();
						$output['use_captcha']=true;
					}
				}
				// would you let me use bbcode?
				if (!empty($config['bbcode_comments'])) {
					$output['bbcode_comments']=true;
				}
				// if we came back after an error get what was previously posted
				if (isset($_SESSION['topass']['input'])) {
					$output=array_merge($output,$_SESSION['topass']['input']);
					unset($_SESSION['topass']['input']);
				}
			} else {
				$output['allow_comments']=false;
			}
		}
	} else {
		$topass['message']['type']=MESSAGE_ERROR;
		$topass['message']['text']='Invalid photo selected';
		redirect2page('info.php',$topass);
	}
} else {
	$topass['message']['type']=MESSAGE_ERROR;
	$topass['message']['text']='Invalid photo selected';
	redirect2page('info.php',$topass);
}

$output['return2me']='photo_view.php';
if (!empty($_SERVER['QUERY_STRING'])) {
	if (!empty($edit_comment)) {
		$_SERVER['QUERY_STRING']=str_replace('&edit_comment='.$edit_comment,'',$_SERVER['QUERY_STRING']);
	}
	$output['return2me'].='?'.$_SERVER['QUERY_STRING'];
}
$output['return2me']=rawurlencode($output['return2me']);

$output['return2']=sanitize_and_format_gpc($_GET,'return',TYPE_STRING,$__html2format[HTML_TEXTFIELD],'');
$output['return']=rawurlencode($output['return2']);

$tpl->set_file('content','photo_view.html');
$tpl->set_var('output',$output);
$tpl->set_loop('loop',$loop);
$tpl->process('content','content',TPL_LOOP | TPL_OPTLOOP | TPL_OPTIONAL);
$tpl->drop_loop('loop');
unset($loop);

$tplvars['title']='View photos';
$tplvars['page_title']=sprintf('%s photos','<a href="profile.php?uid='.$output['fk_user_id'].'">'.$output['user'].'</a>');	// translate this
$tplvars['page']='photo_view';
$tplvars['css']='photo_view.css';
if (is_file('photo_view_left.php')) {
	include 'photo_view_left.php';
}
include 'frame.php';
if (!empty($photo_id) && isset($output['fk_user_id']) && ((isset($_SESSION['user']['user_id']) && $output['fk_user_id']!=$_SESSION['user']['user_id']) || !isset($_SESSION['user']['user_id']))) {
	$query="UPDATE `{$dbtable_prefix}user_photos` SET `stat_views`=`stat_views`+1 WHERE `photo_id`='$photo_id'";
	@mysql_query($query);
}
?>