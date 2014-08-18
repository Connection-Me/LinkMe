<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'controller'.DS.'app.class.php' );

class userController extends coreController
{
	
	function __construct()
	{
		// 载入默认的
		parent::__construct();
	}

	function index()
	{
		//todo
		//i don't know
		$var_dump('hello world');
	}
	
	function login()
	{
		$data['user'] = $_REQUEST['user'];
		$data['pass'] = $_REQUEST['pass'];
		$data['confirm'] = $_REQUEST['confirm'];
		//todo check db and login
		var_dump($data);
		render($data, 'web');
	}
	
	function register()
	{
		$data['user'] = $_REQUEST['user'];
		$data['pass'] = $_REQUEST['pass'];
		//todo insert db and return success
	//	var_dump($data);
		//$ret = '{"ret":"success"}';
		//render($ret, 'user');
		die('{"ret":"success"}');
	}
	
}