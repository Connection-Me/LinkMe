<?php
function return_msg($result_code, $result_msg=null, $data=null)
{
	echo( json_encode( array( 'result_code'=>$result_code, 'result_msg'=>$result_msg, 'data'=>$data)));
} 

function return_message($result_code, $data=null)
{
	echo (json_encode(array('result_code'=>$result_code, 'result_msg'=>$GLOBALS['result'][$result_code], 'data'=>$data_)));
}