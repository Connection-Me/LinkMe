<?php
function md5_salt($str, $salt)
{
	return md5(md5($str, false).'salt'.$salt, false);
}

