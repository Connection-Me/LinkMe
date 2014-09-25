<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'controller'.DS.'app.class.php' );

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
		$endTime = $_REQUEST['endTime'];
		$initTime = time();
		redis_hmset('activity:'.$aid,
		 array('name'=>$name, 'type'=>$type, 'description'=>$description, 'picture'=>$picture,
		 'lowerLimit'=>$lowerLimit, 'upperLimit'=>$upperLimit, 'openTime'=>$openTime,
		  'closeTime'=>$closeTime, 'starter'=>$uid, 'startTime'=>$startTime, 'endTime'=>$endTime,
		 'initTime'=>$initTime));
		$activity = redis_hget('user:'.$uid, 'activity');
		if (!empty($activity))
		{
			$activity = $aid;
		}
		else 
		{
			$activity .= ','.$aid;
		}
		redis_hset('user:'.$uid, 'activity', $activity);
		return_message('0');
		return;
	}
	
	function showList()
	{
	    $sessionId = $_REQUEST['sessionid'];
		$uid = userController::sessionCheck($sessionId);
		if (false == $uid)
		{
			return_message('10005');
			return;
		}
	    $activity = redisr_hget('user:'.$uid, 'activity');
	    $arrayActivity = split(',', $activity);
	    $actList = array();
	    $actCount = 0;
	    forEach($arrayActivity as $aid)
	    {
	    	$act = redis_hmget('activity:'.$aid, array('name', 'initTime', 'startTime', 'approveCount', 'rejectCount', 'picture'));
	        $actCount = array_push($actList, $act);
	    }
	    return_message('0', array('activityCount'=>$actCount, 'activityList'=>$actList));
	    return;
	}
	
	function showDetail()
	{
	    $sessionId = $_REQUEST['sessionid'];
		$uid = userController::sessionCheck($sessionId);
		if (false == $uid)
		{
			return_message('10005');
			return;
		}
		
		$aid = $_REQUEST['aid'];
		$act = $redis_hmget('activity:'.$aid, array('name', 'description', 'startTime', 'stopTime',
		 'inviteList', 'approveList', 'approveCount', 'rejectList',  'rejectCount', 'picture'));
		$arrayApprove = split(',', $act['approveList']);
		$arrayInvite = split(',', $act['inviteList']);
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
		
		return_message('0', $act);
		return;
	}
}