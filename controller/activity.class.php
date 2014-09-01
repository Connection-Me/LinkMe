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
		$sessionid = $_REQUEST['sessionid'];
		
		
		//if sessionid ok
		// redis  incr activityCount
		$name = $_REQUEST['name'];
		$type = $_REQUEST['type'];
		$description = $_REQUEST['description'];
		$picture = $_REQUEST['picture'];
		$lower_limit = $_REQUEST['lower_limit'];
		$upper_limit = $_REQUEST['upper_limit'];
		$open_time = $_REQUEST['open_time'];
		$close_time = $_REQUEST['close_time'];
	}
}