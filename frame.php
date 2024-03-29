<?php
/******************************************************************************
Etano
===============================================================================
File:                       frame.php
$Revision$
Software by:                DateMill (http://www.datemill.com)
Copyright by:               DateMill (http://www.datemill.com)
Support at:                 http://www.datemill.com/forum
*******************************************************************************
* See the "docs/licenses/etano.txt" file for license.                         *
******************************************************************************/

// this file is a simple included file. Most stuff must be defined outside for the main page to function properly.
// you need to include this file in each and every page.

$tpl->set_file('frame','frame.html');
$message=isset($message) ? $message : (isset($topass['message']) ? $topass['message'] : (isset($_SESSION['topass']['message']) ? $_SESSION['topass']['message'] : array()));
if (!empty($message)) {
	$message['type']=(!isset($message['type']) || $message['type']==MESSAGE_ERROR) ? 'message_error' : 'message_info';
	if (is_array($message['text'])) {
		$message['text']=join('<br>',$message['text']);
	}
	$tpl->set_var('message',$message);
}
if (empty($no_timeout)) {
	$_SESSION[_LICENSE_KEY_]['user']['timedout']=array('url'=>(((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']),'method'=>$_SERVER['REQUEST_METHOD'],'qs'=>($_SERVER['REQUEST_METHOD']=='GET' ? $_GET : $_POST));
}

if (is_file(_BASEPATH_.'/events/frame.php')) {
	include_once _BASEPATH_.'/events/frame.php';
}
if (isset($_on_before_display)) {
	for ($i=0;isset($_on_before_display[$i]);++$i) {
		call_user_func($_on_before_display[$i]);
	}
}

$tpl->set_var('tplvars',$tplvars);
if (!empty($page_last_modified_time)) {
	header('Cache-Control: private, max-age=0',true);
	header('Last-Modified: '.date('D,d M Y H:i:s',$page_last_modified_time).' GMT',true);
}
echo $tpl->process('frame','frame',TPL_FINISH | TPL_OPTIONAL | TPL_INCLUDE);
if (isset($_SESSION['topass'])) {
	unset($_SESSION['topass']);
}
ob_end_flush();
if (isset($_on_after_display)) {
	for ($i=0;isset($_on_after_display[$i]);++$i) {
		call_user_func($_on_after_display[$i]);
	}
}
