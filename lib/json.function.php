<?php
function return_msg($result_code, $result_msg=null, $data=null)
{
	echo( json_encode( array( 'result_code'=>$result_code, 'result_msg'=>$result_msg, 'data'=>$data)));
} 