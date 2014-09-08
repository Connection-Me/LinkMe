<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'controller'.DS.'app.class.php' );
include_once( AROOT . 'lib'.DS.'redis.function.php' );
include_once( AROOT . 'lib'.DS.'json.function.php' );
include_once( AROOT . 'lib' . DS . 'crypto.function.php');
class userController extends coreController
{
	private $cache_time;
	function __construct()
	{
		// 载入默认的
		parent::__construct();
		$this->cache_time = 60 * 60 * 24;
	}

	function index()
	{
		echo ('hello world');
		return;
	}
	
	function login()
	{
		$userName = $_REQUEST['user'];
        $userPass = $_REQUEST['pass'];
        //1.检查数据库中有否该用户
        $uid = redis_get('userHash:'.$userName);
        if (isset($uid))
        {
        	//用户未注册
        	return_message('10002');
        	return;
        }
		//2.检查密码是否一致
		$data = redis_hmget('user:'.$uid, array('userPass', 'registTime'));
		$userpass_md5 = md5_salt($userPass, $data['registTime']);
        if ($userpass_md5 != $userPass)
        {
        	return_message('10001');
        	return;
        }
        else 
        {
        	//存在该用户，判断session
        	$sessionId = md5_salt($uid, $registTime); 
        	//替换session并带有生命周期
        	redis_set('session:'.$sessionId, $uid, $this->cache_time);
        	return_message('0', array('session_id'=>$sessionId));
        	return;      	
        }
	}
	
	function regist()
	{
		$username = $_REQUEST['userName'];
		$userpass = $_REQUEST['userPass'];
		//1.检查注册的用户名和密码是否有非法字符
		$tmp1 = preg_match($GLOBALS['preg']['regist'], $username);
		$tmp2 = preg_match($GLOBALS['preg']['regist'], $userpass);
		if (!($tmp1 == 0 && $tmp2 == 0))
		{
			return_message('10003');
			return;
		}
		
		//2.检查注册的用户名是否已被注册
		$uid = redis_get('userHash:'.$username);
		if (empty($uid))
		{
			//该用户名未注册
        	$registTime = time();
        	$userpass_md5 = md5_salt($userpass, $registTime);
        	redis_hmset('user:'.$uid, 
        	    array('userName'=>$username, 'userPass'=>$userpass_md5, 'registTime'=>$registTime));
        	return_message('0');
        	return;
		}
        else 
        {
        	//该用户名已注册
        	return_message('10004');
        	return;
        }
	}
	
    public static function sessionCheck($sessionId)
    {
    	return redis_get('session:'.$sessionId);
    }
}


