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
		echo ('this is LinkMe!');
	//	list($usec, $sec) = explode(" ", microtime());
	//	var_dump($usec);
		return;
	}
	
	function login()
	{
		$userName = $_REQUEST['userName'];
        $userPass = $_REQUEST['userPass'];
        //1.检查数据库中有否该用户
        $uid = self::checkUserExist($userName, 'userName');
        if (empty($uid))
        {
        	//用户未注册
        	return_message('10002');
        	return;
        }
		//2.检查密码是否一致
		$data = redis_hmget('user:'.$uid, array('userPass', 'registTime'));
		$userpass_md5 = md5_salt($userPass, $data['registTime']);
        if ($userpass_md5 != $data['userPass'])
        {
        	return_message('10001');
        	return;
        }
        else 
        {
        	//存在该用户，判断session
        	$sessionId = md5_salt($uid, $data['registTime']); 
        	//替换session并带有生命周期
        	redis_set('session:'.$sessionId, $uid, $this->cache_time);
        	return_message('0', array('session_id'=>$sessionId));
        	logDebug('User Login OK: userName=' . $userName . ' sessionId='.$sessionId, $uid, __METHOD__, __FILE__);
        	return;      	
        }
	}
	
	function regist()
	{
		$userName = $_REQUEST['userName'];
		$userPass = $_REQUEST['userPass'];
		//1.检查注册的用户名和密码是否有非法字符
		$tmp1 = preg_match($GLOBALS['preg']['regist'], $userName);
		$tmp2 = preg_match($GLOBALS['preg']['regist'], $userPass);
		if (!($tmp1 == 0 && $tmp2 == 0))
		{
			return_message('10003');
			return;
		}
		
		//2.检查注册的用户名是否已被注册
		if (false == self::checkUserExist($userName, 'userName'))
		{
			//该用户名未注册
        	$registTime = time();
        	$userpass_md5 = md5_salt($userPass, $registTime);
        	$uid = redis_incr('userCount');
        	redis_hset('userHash', 'userName:'.$userName, $uid);
        	redis_hmset('user:'.$uid, 
        	    array('uid'=>$uid, 'userName'=>$userName, 'userPass'=>$userpass_md5, 'registTime'=>$registTime));	
        	return_message('0');
        	logDebug('Regist New User: uid=' . $uid . ' userName=' . $userName, $uid, __METHOD__, __FILE__);
        	return;
		}
        else 
        {
        	//该用户名已注册
        	return_message('10004');
        	return;
        }
	}
	
	function showUsers()
	{
	    $field = array();
	    array_push($field, 'uid');
		array_push($field, 'userName');
		array_push($field, 'profile');
		array_push($field, 'nickName');
		$userList = userController::getAllUsers($field);
		return_message('0', $userList);
	} 
	
	//$data是所需的字段的array
	public static function getAllUsers($data)
	{
		if (!is_array($data))
		{
			return false;
		}
		$userCount = redis_get('userCount');
		$userList = array();
		for($i = 1; $i <= $userCount; $i++)
		{
			$user = userController::getUser($i, $data);
			if (!empty($user))
			{
				array_push($userList, $user);
			}
		}
		return $userList;
	}
	
	//$data是所需的字段的array
	public static function getUser($uid, $data)
	{
		if (!is_array($data))
		{
			return false;
		}
		return redis_hmget('user:'.$uid, $data);
	}
	
    public static function sessionCheck($sessionId)
    {
    	return redis_get('session:'.$sessionId);
    }

    public static function checkUserExist($data, $way = 'id')
    {
    	$uid = null;
    	if ('id' == $way)
    	{
    		$uid = redis_hget('user:'.$data, 'uid');
    		
    	}
    	else if ('userName' == $way)
    	{
    		$data = 'userName:'.$data;
    		$uid = redis_hget('userHash', $data);
    	}
    	else if ('nickName' == $way)
    	{
    		$data = 'nickName:'.$data;
    		$uid = redis_hget('userHash', $data);
    	}
        if (empty($uid))
    	{
    	    return false;
    	}
    	//成功找到该用户，则返回其uid
    	return $uid;
    }
    
    //设置用户兴趣
    function setHobby()
    {
        $sessionId = $_REQUEST['sessionId'];
		$uid = userController::sessionCheck($sessionId);
		if (false == $uid)
		{
			return_message('10005');
			return;
		}
		
		$hobbies = $_REQUEST['hobbies'];
		$hobby_list = preg_split('/_/', $hobbies);
		$hobby_conf = $GLOBALS['jsonconfig']['hobby'];
		$hobby_array = array();
		foreach($hobby_list as $h)
		{
			if (empty($hobby_conf[$h]))
			{
				continue;
			}
			$hobby_array['hobby:'.$h] = 1;
		}
		foreach($hobby_conf as $h)
		{
			$hid = $h['hid'];
			if (empty($hobby_array['hobby:'.$hid]))
			{
				$hobby_array['hobby:'.$hid] = 0;
			}
		}
		//var_dump($hobby_array);
		redis_hmset('user:'.$uid, $hobby_array);
        return_message('0');
        logDebug('Set Hobby: hobbies=' . $hobbies, $uid, __METHOD__, __FILE__);
        return;
    }
    
    //获取用户兴趣
    function showHobby()
    {
        $sessionId = $_REQUEST['sessionId'];
		$uid = userController::sessionCheck($sessionId);
		if (false == $uid)
		{
			return_message('10005');
			return;
		}
		
        $hobby_conf = $GLOBALS['jsonconfig']['hobby'];
		$hobby_array = array();
		foreach($hobby_conf as $h)
		{
			$hid = $h['hid'];
		    array_push($hobby_array, 'hobby:'.$hid);
		}
		$hobby_list = redis_hmget('user:'.$uid, $hobby_array);
		$ret_list = '';
        foreach($hobby_conf as $h)
		{
			$hid = $h['hid'];
			if (1 != $hobby_list['hobby:'.$hid])
			{
				continue;
			}
			if ('' != $ret_list)
			{
				$ret_list .= '_';
			}
			$ret_list .= $hid;		    
		}
		return_message('0', array('hobbies' => $ret_list));
		logDebug('Show Hobby: hobbies=' . $ret_list, $uid, __METHOD__, __FILE__);
		return;
    }
    
    //设置用户基础信息  （头像、昵称等）
    function setUserDetail()
    {
        $sessionId = $_REQUEST['sessionId'];
		$uid = userController::sessionCheck($sessionId);
		if (false == $uid)
		{
		    return_message('10005');
			return;
		}
		$data = array();
		$data['profile'] = $_REQUEST['profile'];
		$data['nickName'] = $_REQUEST['nickName'];
		$data['gender'] = $_REQUEST['gender'];
		$data['age'] = $_REQUEST['age'];
		$data['cellphone'] = $_REQUEST['cellphone'];
		$data['qq'] = $_REQUEST['qq'];
		$data['email'] = $_REQUEST['email'];
		$data['weibo'] = $_REQUEST['weibo'];
		$data['wechat'] = $_REQUEST['wechat'];
		foreach($data as $key=>$val)
		{
			if(empty($val))
			{
				unset($data[$key]);
			}
		}
		if (!empty($data))
		{
			redis_hmset('user:'.$uid, $data);
		}
		return_message('0');
		return;
    }
    
    //查询用户基础信息  （头像、昵称等）
    function showUserDetail()
    {
        $sessionId = $_REQUEST['sessionId'];
		$uid = userController::sessionCheck($sessionId);
		if (false == $uid)
		{
		    return_message('10005');
			return;
		}
		$target = $_REQUEST['target'];
		$way = $_REQUEST['way'];
		$targetId = userController::checkUserExist($target, $way);
        if (false == $targetId)
		{
		    return_message('2');
		    return;
		}
		$data = array('uid', 'profile', 'nickName', 'gender', 'age', 'cellphone', 'qq', 'email', 'weibo', 'wechat');
		$result = redis_hmget('user:'.$targetId, $data);
		return_message('0', $result);
		return;
    }
    
    function recommend()
	{
	    $sessionId = $_REQUEST['sessionId'];
		$uid = userController::sessionCheck($sessionId);
		if (false == $uid)
		{
			return_message('10005');
			return;
		}
		$hobby = $_REQUEST['hobby'];
		$limit = $_REQUEST['limit'];
		$offset = $_REQUEST['offset'];
		
	    $userCount = redis_get('userCount');
		$hobby_conf = $GLOBALS['jsonconfig']['hobby'];
		$data = array(
		    'uid' ,'nickName', 'profile'
		);
	    foreach($hobby_conf as $h)
		{
			$hid = $h['hid'];
			array_push($data, 'hobby:'.$hid);
		}
	    $userList = array();
	    $count = 0;
		for($i = 1 + $offset; $i <= $userCount; $i++)
		{
			$user = userController::getUser($i, $data);
			if(0 != $hobby && 1 != $user['hobby:'.$hobby])
		    {
		    	continue;
		    }
		    $tmp = array();
		    $tmp['uid'] = $user['uid'];
		    $tmp['nickName'] = $user['nickName'];
		    $tmp['profile'] = $user['profile'];
		    array_push($userList, $tmp);
		    $count++;
		    if ($count >= $limit)
		    {
		    	break;
		    }    
		}
		return_message('0', $userList);
		return;
	}
	
	function getWechatAccessToken()
	{
		$code = $_REQUEST['code'];
		$appid = $GLOBALS['jsonconfig']['wechat']['appid'];
		$appsecret = $GLOBALS['jsonconfig']['wechat']['appsecret'];
		$grant_type = $GLOBALS['jsonconfig']['wechat']['grant_type'];
		$url='https://api.weixin.qq.com/sns/oauth2/access_token?';
		$url = $url . 'appid=' . $appid . '&secret=' . $appsecret . '&code=' . $code
		    . '&grant_type=' . $grant_type;
		echo($url);

		$ch = curl_init();
        $timeout = 5;
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $res= curl_exec($ch);
        curl_close($ch);
		
	    var_dump($res);
	    $res = json_decode($res);
	    if (null == $res['errcode'])
	    {
	    	//success
	    	$access_token = $res['access_token'];
	    	$expires_in = $res['expires_in']; // expire time(in seconds)
	    	$refresh_token = $res['refresh_token'];
	    	$openid = $res['openid'];
	    	$scope = $res['scope'];
	    	//todo save to db and do something
	    }
	    else 
	    {
	    	$errcode = $res['errcode'];
	    	$errmsg = $res['errmsg'];
	    }
	    //return_message(0);
	    return;
	}
}


