<?php

use App\Home\Models\Article;
use QuicCode\View\View;
use QuicCode\Controller\Controller;

class IndexController extends Controller{




	public function index()
	{
		$a = Article::all();

		$this->view = View::display('index')->add('a',$a);
	}



}