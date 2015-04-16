<?php
use QuicCode\Mvc\Mvc;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Routing\Dispatcher;
use ICanBoogie\Routing\Routes;



require '../vendor/autoload.php';

//定义root
define("ROOT_PATH",str_replace("\\","/",dirname(dirname(__FILE__))));
//定义base
define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");

define('APP_PATH',ROOT_PATH."/App/Home/");

//Mvc::Run();

Mvc::Run();



