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
		$redis = new Redis();
		if (!$redis->connect($host, $port))
		{
			// connect failed
			halt('redis connect failed!');
			return null;
		}
		else 
		{
			$GLOBALS['redis'] = $redis;
			return $GLOBALS['redis'];
		}
	}
	else 
	{
		return $GLOBALS['redis'];
	}
	return null;
}

function redis_get($key)
{
	if ($redis == null) $redis = getRedis();
	return $redis->get($key);
}

function redis_set($key, $value, $expire = null)
{
	if ($redis == null) $redis = getRedis();
	if ($expire == null)
	{
		return $redis->set($key, $value);
	}
	else 
	{
		return $redis->setex($key, $expire, $value);
	}
}

function redis_hget($h, $key)
{
	if ($redis == null) $redis = getRedis();
	return $redis->hGet($h, $key);
}

function redis_hset($h, $key, $value)
{
	if ($redis == null) $redis = getRedis();
	return $redis->hSet($h, $key, $value);
}

function redis_hmset($h, $data)
{
	if ($redis == null) $redis = getRedis();
	if (!is_array($data))
	{
		return false;
	}
	return $redis->hMset($h, $data);
}

function redis_hmget($h, $data)
{
	if ($redis == null) $redis = getRedis();
	if (!is_array($data))
	{
		return array();
	}
	return $redis->hMget($h, $data);
}

function redis_incr($key)
{
	if ($redis == null) $redis = getRedis();
	return $redis->incr($key);
}
