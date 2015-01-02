<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'controller'.DS.'app.class.php' );
include_once( AROOT . 'controller'.DS.'user.class.php' );
include_once( AROOT . 'lib'.DS.'common.function.php');
include_once( AROOT . 'qiniu'.DS.'rs.php');

class qiniuController extends coreController
{
    function __construct()
	{
		// 载入默认的
		parent::__construct();
	}
	
	function getToken()
	{
	    $sessionId = $_REQUEST['sessionId'];
		$uid = userController::sessionCheck($sessionId);
		if (false == $uid)
		{
			return_message('10005');
			return;
		}
		$access = $GLOBALS['jsonconfig']['qiniu']['access'];
		$secret = $GLOBALS['jsonconfig']['qiniu']['secret'];
	    $bucket = $GLOBALS['jsonconfig']['qiniu']['bucket'];
	    Qiniu_SetKeys($accessKey, $secretKey);
	    $putPolicy = new Qiniu_RS_PutPolicy($bucket);
	    $upToken = $putPolicy->Token(null);
	    return_message('0', array('token'=>$upToken));
	    return;	
	}
}