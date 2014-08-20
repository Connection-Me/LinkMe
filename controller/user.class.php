<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'controller'.DS.'app.class.php' );
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
		$redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $userpass = $redis->hGet('user:'.$user, 'pass');
        $registTime = $redis->hGet('user:'.$user, 'registTime'); 
        if (empty($userpass) || $userpass != $pass)
        {
        	echo($callback.'("result":1)');
        }
        else 
        {
        	//存在该用户，判断session
        	$sessionId = md5_salt($user, $registTime); 
        	//var_dump('session:'.$sessionId);
        	if (!$redis->get('session:'.$sessionId))
        	{
        		//用户session已失效,设置一个有时限的session
        		$redis->setex('session:'.$sessionId, $this->cache_time, $user);
        	}
        	else 
        	{
        		//删除原有session，替换一个新的session
        		$redis->delete('session:'.$sessionId);
        		$redis->setex('session:'.$sessionId, $this->cache_time, $user);
        	}
        	echo($callback.'("result":0)');      	
        }
	}
	
	function regist()
	{
		$user = $_REQUEST['user'];
		$pass = $_REQUEST['pass'];
		$callback = $_REQUEST['callback'];
		$redis = new Redis();
        $redis->connect('127.0.0.1',6379);
	    if ($redis->hGet('user:'.$user, 'user'))
        {
        	//该用户名已注册
        	echo($data['callback'].'("result":1)');
        }
        else 
        {
        	//该用户名未注册
        	$registTime = time();
        	$redis->hMset('user:'.$user, 
        	    array('user'=>$user, 'pass'=>$pass, 'registTime'=>$registTime));
        	echo($data['callback'].'("result":0)');
        }
	}
	
}