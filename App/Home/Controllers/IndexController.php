<?php

use App\Home\Models\Article;
use quiccode\View\View;
use quiccode\Controller\Controller;

class IndexController extends Controller{




	public function index()
	{
		$a = Article::all();

		$this->view = View::display('index');
	}



}