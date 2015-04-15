<?php namespace quiccode\Mvc;

use quiccode\Route\Route;


class Mvc{

	public static function Run()
	{
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