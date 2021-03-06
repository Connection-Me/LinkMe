<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'controller'.DS.'app.class.php' );
include_once( AROOT . 'controller'.DS.'user.class.php');
include_once( AROOT . 'lib'.DS.'common.function.php');

class activityController extends coreController
{
    function __construct()
	{
		// 载入默认的
		parent::__construct();
	}
	
	function create()
	{
		$sessionId = $_REQUEST['sessionId'];
		$uid = userController::sessionCheck($sessionId);
		if (empty($uid))
		{
			return_message('10005');
			return;
		}
		// redis  incr activityCount
		$aid = redis_incr('activityCount');
		$name = $_REQUEST['name'];
		$type = $_REQUEST['type'];
		$description = $_REQUEST['description'];
		$picture = $_REQUEST['picture'];
		$lowerLimit = $_REQUEST['lowerLimit'];
		$upperLimit = $_REQUEST['upperLimit'];
		$openTime = $_REQUEST['openTime'];
		$closeTime = $_REQUEST['closeTime'];
		$startTime = $_REQUEST['startTime'];
		$stopTime = $_REQUEST['endTime'];
		$initTime = time();
		
		if (empty($startTime))
		{
			return_message('1');
			return;
		}
				
		redis_hmset('activity:'.$aid,
		 array('aid'=>$aid, 'name'=>$name, 'type'=>$type, 'description'=>$description, 'picture'=>$picture,
		 'lowerLimit'=>$lowerLimit, 'upperLimit'=>$upperLimit, 'openTime'=>$openTime,
		  'closeTime'=>$closeTime, 'starter'=>$uid, 'startTime'=>$startTime, 'stopTime'=>$stopTime,
		 'initTime'=>$initTime));
		$activity = redis_hget('user:'.$uid, 'activityList');
		if (empty($activity))
		{
			$activity = $aid;
		}
		else 
		{
			$activity .= ','.$aid;
		}
		redis_hset('user:'.$uid, 'activityList', $activity);
		
		//以开始顺序
		redis_zadd('activityStart:'.$uid, $startTime, $aid);
		
		return_message('0', array('aid'=>$aid));
		return;
	}
	
	function showList()
	{
	    $sessionId = $_REQUEST['sessionId'];
		$uid = userController::sessionCheck($sessionId);
		if (false == $uid)
		{
			return_message('10005');
			return;
		}
		
		//var_dump($arrayActivity);
	    //$activity = redis_hget('user:'.$uid, 'activityList');
	    //$arrayActivity = preg_split('/,/', $activity);
	    
	    $actCount = 0;
	    $when = $_REQUEST['when'];
	    $way = $_REQUEST['way'];
	    $offset = $_REQUEST['offset'];
	    $limit = $_REQUEST['limit'];
	    $actList = array();
	    $arrayActivity = array();
	    if ('todo' == $when || 'doing' == $when)
	    {
	    	$arrayActivity = redis_zrange('activityStart:'.$uid, 0, -1);
	    }
	    else if('done' == $when)
	    {
	    	$arrayActivity = redis_zrevrange('activityStart:'.$uid, 0, -1);
	    }
	    forEach($arrayActivity as $aid)
	    {
	    	$act = redis_hmget('activity:'.$aid, array('aid', 'name', 'initTime', 'startTime', 'approveCount', 'rejectCount', 'picture',
	    	'starter', 'stopTime', 'openTime', 'closeTime'));
	    	
	        if (!activityController::checkActivityWhen($act, $when))
	        {
	        	continue;	
	       	}
	    	
	        if ('all' == $way) //查询所有自己参加的活动
	        {
	    	    $actCount = array_push($actList, $act);
	        }
	        else if ('host' == $way)  //所有自己是发起人的活动
	        {
	            if ($act['starter'] == $uid)
	            {
	            	$actCount = array_push($actList, $act);
	            }
	        }
	        else if ('guest' == $way) //所有自己参加的非发起人的活动
	        {
	        	if ($act['starter'] != $uid)
	        	{
	        		$actCount = array_push($actList, $act);
	        	}
	        }	
	    }
	    $retList = array();
	    $count = count($actList);
	    $retCount = 0;
	    for($i = $offset; $i < $count && $i < $offset + $limit; $i++)
	    {
	    	$act = getObject($actList[i], array('aid', 'name', 'initTime', 'startTime', 'approveCount', 'rejectCount', 'picture'));
	    	$retCount = array_push($retList, $actList[$i]);
	    }
	    return_message('0', array('activityCount'=>$retCount, 'activityList'=>$retList));
	    return;
	}
	
	public static function checkActivityWhen($activity, $when)
	{
		$now = time();
		if ('todo' == $when)
		{
		    $startTime = $activity['startTime'];
		    if ($now < $startTime)
		    {
		    	return true;
		    }
		}
		else if ('doing' == $when)
		{
			$startTime = $activity['startTime'];
			$stopTime = $activity['stopTime'];
			if ($now > $startTime && $now < $stopTime)
			{
				return true;
			}
		}
		else if ('done' == $when)
		{
			$stopTime = $activity['stopTime'];
			if ($now > $stopTime)
			{
				return true;
			}
		}
		else if ('all' == $when)
		{
			return true;
		}
		return false;
	}
	
	function showDetail()
	{
	    $sessionId = $_REQUEST['sessionId'];
		$uid = userController::sessionCheck($sessionId);
		if (false == $uid)
		{
			return_message('10005');
			return;
		}
		
		$aid = $_REQUEST['aid'];
		$act = redis_hmget('activity:'.$aid, array('aid', 'name', 'description', 'startTime', 'stopTime', 'openTime', 'closeTime',
		 'inviteList', 'approveList', 'approveCount', 'rejectList',  'rejectCount', 'picture', 'starter'));
		$arrayApprove = preg_split('/,/', $act['approveList']);
		$arrayInvite = preg_split('/,/', $act['inviteList']);
		$apprList = array();
		$inviList = array();
		foreach ($arrayApprove as $apprUser)
		{
			$appr = redis_hmget('user:'.$apprUser, array('uid', 'nickName', 'profile'));
			array_push($apprList, $appr);
		}
		foreach ($arrayInvite as $inviUser)
		{
			$invi = redis_hmget('user:'.$apprUser, array('uid', 'nickName', 'profile'));
		    array_push($inviList, $invi);
		}
		$act['approveList'] = $arrayApprove;
		$act['inviteList'] = $arrayInvite;
		
		$starter = $act['starter'];
		$starterUser = redis_hmget('user:'.$starter, array('uid', 'nickName', 'profile'));
	    $act['starter'] = $starterUser;
		
		return_message('0', $act);
		return;
	}
	
	function invite()
	{
	    $sessionId = $_REQUEST['sessionId'];
		$uid = userController::sessionCheck($sessionId);
		if (false == $uid)
		{
			return_message('10005');
			return;
		}
		
	    $aid = $_REQUEST['aid'];
		$act = redis_hmget('activity:'.$aid, array('starter', 'startTime', 'inviteCount', 'inviteList'));
		if (empty($act) || $uid != $act['starter'])
		{
			//发起人不是该用户，无法邀请
			return_message('20002');
			return;
		}
		$now = time();
		if ($now > $act['startTime'])
		{
			//活动已开始，无法邀请
			return_message('20003');
			return;
		}
		$inviteList = $act['inviteList'];
		$inviteArray = preg_split('/,/', $inviteList);
		
		$inviteUser = $_REQUEST['inviteUser'];
		$inviteUserArray = preg_split('/,/', $inviteUser);
		$way = $_REQUEST['way']; //使用uid或userName或nickName
		if (empty($inviteUser))
		{
			return_message('1');
			return;
		}
		$data = array(
		    'inviteCount'=>count($inviteArray),
		    'inviteList'=>$inviteList
		);
		foreach ($inviteUserArray as $tmp)
		{
		    if ($tmp == $uid)
		    {
		    	//不能邀请自己
			    continue;
		    }
		    $inviteUser = userController::checkUserExist($tmp, $way);
		    if (false == $inviteUser)
		    {
			    continue; //邀请失败，找不到该用户
		    }
		    $flag = false;
		    foreach ($inviteArray as $inv)
		    {
		        if ($inv == $inviteUser)
				{
					//该用户已被邀请
					$flag = true;
					break;
				}
		    }
		    if ($flag)
		    {
		    	continue;
		    }
		    if ($data['inviteCount'] == 0)
		    {
		    	$data['inviteCount'] = 1;
			    $data['inviteList'] = $inviteUser;
		    }
		    else 
		    {
		    	$data['inviteCount'] ++;
			    $data['inviteList'] .=  (',' . $inviteUser);
		    }
		    
		    $userInvite = redis_hget('user:'.$inviteUser, 'inviteList');
		    if (empty($userInvite))
		    {
			    redis_hset('user:'.$inviteUser, 'inviteList', $aid);
		    }
		    else 
		    {
			    redis_hset('user:'.$inviteUser, 'inviteList', $userInvite . ',' . $aid);
		    }
		}			
		redis_hmset('activity:'.$aid, $data);  
	    
		return_message('0');
		return;
	}
	
	
	function checkInvite()
	{
		//客户端每10秒请求一次
	    $sessionId = $_REQUEST['sessionId'];
		$uid = userController::sessionCheck($sessionId);
		if (false == $uid)
		{
			return_message('10005');
			return;
		}
		
		$inviteList = redis_hget('user:'.$uid, 'inviteList');
		if (empty($inviteList))
		{
			return_message('0');
			return;
		}
		
		$inviteArray = preg_split('/,/', $inviteList);
		$data = array();
		foreach ($inviteArray as $aid)
		{
			$act = redis_hmget('activity:'.$aid, array('aid', 'name', 'initTime', 'startTime', 
			'approveCount', 'rejectCount', 'picture', 'starter'));
		    $starter = $act['starter'];
		    $act['starter'] = redis_hget('user:'.$starter, 'nickName');
		    array_push($data, $act);
		}
		return_message('0', $data);
		return;
	}
	
	function checkInviteNum()
	{
		$sessionId = $_REQUEST['sessionId'];
		$uid = userController::sessionCheck($sessionId);
		if (false == $uid)
		{
			return_message('10005');
			return;
		}
		
		$inviteList = redis_hget('user:'.$uid, 'inviteList');
		if (empty($inviteList))
		{
			return_message('0');
			return;
		}
		
		$inviteArray = preg_split('/,/', $inviteList);
		$num = count($inviteArray);
		return_message('0', array('num'=>$num));
		return;
	}
	
	function acceptInvite()
	{
	    $sessionId = $_REQUEST['sessionId'];
		$uid = userController::sessionCheck($sessionId);
		if (false == $uid)
		{
			return_message('10005');
			return;
		}
		
		$aid = $_REQUEST['aid'];
		
		$inviteList = redis_hget('user:'.$uid, 'inviteList');
		$inviteArray = preg_split('/,/', $inviteList);
		$inviteListTmp = '';
		$flag = false;
	    foreach ($inviteArray as $actId)
		{
			if ($actId == $aid)
			{
				$flag = true;
				continue; 
			}
			if ($inviteListTmp != '')
			{
				$inviteListTmp .= ',';
			}
			$inviteListTmp .= $actId;
		}
		if (false == $flag)
		{
			return_message('20006');
			return;
		}
		$activity = redis_hmget('activity:'.$aid, array('starter','approveList','approveCount','startTime'));
		$now = time();
		if ($now >= $activity['startTime'])
		{
			return_message('20008');
		}
		$approveList = $activity['approveList'];
		$approveArray = preg_split('/,/', $approveList);
		foreach ($approveArray as $apv)
		{
			if ($apv == $uid)
			{
			    return_message('20007');
			    return;
			}
		}
		if ($approveList != '')
		{
			$approveList .= ',';
		}
		$approveList .= $uid;
		$approveCount = $activity['approveCount'] + 1;
		redis_hmset('activity:'.$aid, array('approveList'=>$approveList, 'approveCount'=>$approveCount));
	    redis_hmset('user:'.$uid, array('inviteList'=>$inviteListTmp));
	    return_message('0');
	    return;
	}
}