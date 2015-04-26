<?php
/**** echo utf8 ****/
header("Content-Type: text/html;charset=utf-8");

include_once( AROOT . 'lib'.DS.'json.function.php' );

$GLOBALS['config']['site_name'] = 'LazyPHP3';
$GLOBALS['config']['site_domain'] = 'lazyphp3.sinaapp.com';
//$GLOBALS['redis'] = new Redis();
//$redis->connect('127.0.0.1',6379);

$GLOBALS['preg']['regist'] = '[a-zA-Z0-9_@.]'; //允许字母，数字和'_' '@' '.'
$GLOBALS['jsonconfig']['result'] = config_reader('jsonconfig', 'result', 'json');
$GLOBALS['jsonconfig']['hobby'] = config_reader('jsonconfig', 'hobby', 'json');
$GLOBALS['jsonconfig']['qiniu'] = config_reader('jsonconfig', 'qiniu', 'json');
$GLOBALS['jsonconfig']['wechat'] = config_reader('jsonconfig', 'wechat', 'json');

$GLOBALS['log']['addr'] = AROOT . 'log';