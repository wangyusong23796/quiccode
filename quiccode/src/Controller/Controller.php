<?php namespace quiccode\Controller;

use quiccode\View\View;


class Controller{

	public $view;


	public function __construct()
  	{
  		//echo '初始化成功!';

  	}


	public function __destruct()
    {
    	
        $view = $this->view;

        if($view instanceof View){
          if(!empty($view->data))
          {

             extract($view->data);	
          }
          require $view->view;
        }
    }


}