<?php
function return_msg($result_code, $result_msg=null, $data=null)
{
	echo( json_encode( array( 'result_code'=>$result_code, 'result_msg'=>$result_msg, 'data'=>$data)));
} 

function return_message($result_id, $data=null)
{
	echo (json_encode(array('result_code'=>$GLOBALS['jsonconfig']['result'][$result_id]['result_code'], 
	'result_msg'=>$GLOBALS['jsonconfig']['result'][$result_id]['result_msg'], 'data'=>json_encode($data))));
}

function config_reader($father, $name, $suffix)
{
    $filename = AROOT . $father . DS . $name . DOT . $suffix;
	$json_str = file_get_contents($filename);
	$json_array = json_decode($json_str, true);
	if (!is_array($json_array))
	{
		//log
		print_r('can not load json file: ' . $filename);
		return null;
	}
	return $json_array;
}


