<?php

if( !defined('debug') ) define( 'debug' , DEBUG );
if( !defined('error') ) define( 'error' , ERROR );
if( !defined('warn') ) define( 'warn' , WARN );
if( !defined('info') ) define( 'info' , INFO );
if( !defined('trace') ) define( 'trace' , TRACE );

date_default_timezone_set('Asia/Shanghai');  //设置时区

function logDebug($str, $uid, $method, $file)
{
	logGenerator(DEBUG, $str, $uid, $method, $file);
}

function logError($str, $uid)
{
	logWriteLocal(ERROR, $str);
}

function logWarn($str, $uid)
{
	logWriteLocal(WARN, $str);
}

function logInfo($str, $uid)
{
	logWriteLocal(INFO, $str);
}

function logTrace($str, $uid)
{
	logWriteLocal(TRACE, $str);
}

function logGenerator($type, $str, $uid, $method, $file)
{
	list($usec, $sec) = explode(" ", microtime());
	$time = date('H:i:s', $sec) . '.' . ($usec * 100000);
	$file = basename($file);
	//$str = '|' . date_format('H:i:s.u') . '|' . basename($file) . '|' . $method . '|' . $uid . '|' . ' : ' . $str . "\r\n";
	$str = sprintf("%s|%s|%s|%s|: %s\r\n", $time, $uid, $file, $method, $str);
	logWriteLocal($type, $str);
}

function logWriteLocal($type, $str)
{
	file_put_contents($GLOBALS['log']['addr'] . DS . $type . DS . date('YmdH') . '.log', $str, FILE_APPEND);	
}

