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
	    $arrayActivity = array();
	    if (empty($activity))
	    {
	        $arrayActivity = $activity.split(',', $activity);
	    }
	    $actList = array();
	    $actCount = 0;
	    forEach($arrayActivity as $aid)
	    {
	    	$act = $redis_hmget('activity:'.$aid, array('name', 'initTime', 'startTime', 'approve', 'reject', 'picture'));
	        $actCount = array_push($actList, $act);
	    }
	    return_message('0', array('activityCount'=>$actCount, 'activityList'=>$actList));
	    return;
	}
}