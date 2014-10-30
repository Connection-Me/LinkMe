<?php
function getObject($array, $data)
{
	//从一个多余的大数组中获取确定的字段，返回一个数组
	$ret = array();
	if (!is_array($array) || !is_array($data))
	{
		return $ret;
	}
	foreach ($data as $d)
	{
		$ret[$d] = $array[$d];
	}
	return $ret;
}