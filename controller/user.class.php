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
	}
	
	function login()
	{
		$user = $_REQUEST['user'];
        $pass = $_REQUEST['pass'];
		$callback = $_REQUEST['callback'];
		//todo check db and login
		$data = redis_hmget('user:'.$user, array('pass', 'registTime')); 
        if (isset($data['pass']) || $data['pass'] != $pass)
        {
        	return_msg('10001', '密码不正确');
        }
        else 
        {
        	//存在该用户，判断session
        	$sessionId = md5_salt($user, $registTime); 
        	//替换session并带有生命周期
        	redis_set('session:'.$sessionId, $user, $this->cache_time);
        	return_msg('0', '操作成功', json_encode(array('session_id'=>$sessionId)));      	
        }
	}
	
	function regist()
	{
		$user = $_REQUEST['user'];
		$pass = $_REQUEST['pass'];
		$redis = getRedis();
	    if (redis_hget('user:'.$user, 'user'))
        {
        	//该用户名已注册
        	return_msg('xxxxxx', '用户名已注册');
        }
        else 
        {
        	//该用户名未注册
        	$registTime = time();
        	redis_hmset('user:'.$user, 
        	    array('user'=>$user, 'pass'=>$pass, 'registTime'=>$registTime));
        	return_msg('0', '操作成功');
        }
	}
}