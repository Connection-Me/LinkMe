<?php
//redis
function getRedis($host = null, $port = null)
{
	if (!isset( $GLOBALS['redis'] ))
	{
		include_once (AROOT . DS . 'config' . DS . 'db.config.php');
		$redis_config = $GLOBALS['config']['redis'];
		
		if (null == $host) $host = $redis_config['host'];
		if (null == $port) $port = $redis_config['port'];
		
		if (!$GLOBALS['redis']->connect($host, $port))
		{
			// connect failed
			halt('redis connect failed!');
			return null;
		}
		else 
		{
			return $GLOBALS['redis'];
		}
	}
	return null;
}