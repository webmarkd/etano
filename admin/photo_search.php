<?php
/******************************************************************************
newdsb
===============================================================================
File:                       admin/photo_search.php
$Revision$
Software by:                DateMill (http://www.datemill.com)
Copyright by:               DateMill (http://www.datemill.com)
Support at:                 http://forum.datemill.com
*******************************************************************************
* See the "softwarelicense.txt" file for license.                             *
******************************************************************************/

require_once '../includes/sessions.inc.php';
require_once '../includes/classes/phemplate.class.php';
require_once '../includes/vars.inc.php';
require_once '../includes/admin_functions.inc.php';
db_connect(_DBHOSTNAME_,_DBUSERNAME_,_DBPASSWORD_,_DBNAME_);
allow_dept(DEPT_ADMIN);

$tpl=new phemplate('skin/','remove_nonjs');

$profile=array();
$profile['pstat']=vector2options($accepted_pstats);

$tpl->set_file('content','photo_search.html');
$tpl->set_var('profile',$profile);

$tpl->process('content','content',TPL_LOOP);

$tplvars['title']='Search';
include 'frame.php';
?>