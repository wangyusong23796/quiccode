<?php namespace quiccode\Mvc;

use quiccode\Route\Route;
use Illuminate\Database\Capsule\Manager as database;


class Mvc{

	public static function Run()
	{
			//注册数据库..
		
			$capsule = new database;
			$capsule->addConnection(require ROOT_PATH.'/config/database.php');
			$capsule->bootEloquent();

			require APP_PATH."config/routes.php";
			//注册视图
			$whoops = new \Whoops\Run;
			$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
			$whoops->register();
			//注册出错路由.

			Route::$error_callback = function() {
			  	throw new Exception("路由无匹配项 404 Not Found");
			};

			Route::dispatch();
	}

}