<?php

use App\Home\Models\Article;
use QuicCode\View\View;
use QuicCode\Controller\Controller;
use QuicCode\Http\Request;

class IndexController extends Controller{




	public function index()
	{
		
		$a = Article::all();

		$this->view = View::display('index')->add('a',$a);
	}

	public function postindex()
	{
		$request = new Request();
		var_dump($request->all());
		echo $request->input('nihao');
		echo $request->method();
	}



}