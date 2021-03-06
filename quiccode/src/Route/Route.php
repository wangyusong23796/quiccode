<?php namespace QuicCode\Route;

class Route
{

    public static $halts = false;

    public static $routes = array();
    //设置hooks
    public static $hook = array();

    public static $methods = array();

    public static $callbacks = array();

    public static $patterns = array(
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*'
    );

    public static $error_callback;

    /**
     * Defines a route w/ callback and method
     */
    public static function __callstatic($method, $params) 
    {
        
        $uri = $params[0];
        $callback = $params[1];
        if (!empty($params[2])) {
            # code...
            $hooks = $params[2];
            array_push(self::$hook,$hooks);
        }
        
        //第三个参数 设置hook

        array_push(self::$routes, $uri);
        array_push(self::$methods, strtoupper($method));
        array_push(self::$callbacks, $callback);

    }

    /**
     * Defines callback if route is not found
    */
    public static function error($callback)
    {
        self::$error_callback = $callback;
    }
    
    public static function haltOnMatch($flag = true)
    {
        self::$halts = $flag;
    }

    /**
     * Runs the callback for the given request
     */
    public static function dispatch()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];  

        $searches = array_keys(static::$patterns);
        $replaces = array_values(static::$patterns);

        $found_route = false;

        // check if route is defined without regex
        if (in_array($uri, self::$routes)) {
            $route_pos = array_keys(self::$routes, $uri);

            foreach ($route_pos as $route) {

                if (self::$methods[$route] == $method) {

                    $found_route = true;

                    //if route is not an object 
                    if(!is_object(self::$callbacks[$route])){

                        //grab all parts based on a / separator 
                        $parts = explode('\\',self::$callbacks[$route]);

                        $path = '';
                            switch (count($parts)) {
                                case '2':
                                    # code...
                                    $path = "/controllers/".$parts[0]."/";
                                    break;
                                case '3':
                                    $path = "/controllers/".$parts[0]."/".$parts[1]."/";
                                    break;
                                case '4':
                                    $path = "/controllers/".$parts[0]."/".$parts[1]."/".$parts[2]."/";
                                    break;
                                case '5':
                                    $path = "/controllers/".$parts[0]."/".$parts[1]."/".$parts[2]."/".$parts[3]."/";
                                    break;
                                default:
                                    # code...
                                    $path = "/controllers/";
                                    break;
                            }




                            //collect the last index of the array
                            $last = end($parts);

                            //grab the controller name and method call
                            $segments = explode('@',$last);

                            require APP_PATH.$path.$segments[0].".php";
                            // 控制器前面的钩子
                            //instanitate controller
                            $controller = new $segments[0]();


                            //方法前面的钩子
                            //call method
                            $controller->$segments[1](); 

                            if (self::$halts) return;
                          
                    } else {
                        //call closure
                        call_user_func(self::$callbacks[$route]);
                        
                        if (self::$halts) return;
                    }






                }
            }
        } else {
            // check if defined with regex
            $pos = 0;
            foreach (self::$routes as $route) {

                if (strpos($route, ':') !== false) {
                    $route = str_replace($searches, $replaces, $route);
                }

                if (preg_match('#^' . $route . '$#', $uri, $matched)) {
                    if (self::$methods[$pos] == $method) {
                        $found_route = true;

                        array_shift($matched); //remove $matched[0] as [1] is the first parameter.


                        if(!is_object(self::$callbacks[$pos])){

                            //grab all parts based on a / separator 
                            $parts = explode('\\',self::$callbacks[$pos]); 
                            $path = '';
                            switch (count($parts)) {
                                case '2':
                                    # code...
                                    $path = "/controllers/".$parts[0]."/";
                                    break;
                                case '3':
                                    $path = "/controllers/".$parts[0]."/".$parts[1]."/";
                                    break;
                                case '4':
                                    $path = "/controllers/".$parts[0]."/".$parts[1]."/".$parts[2]."/";
                                    break;
                                case '5':
                                    $path = "/controllers/".$parts[0]."/".$parts[1]."/".$parts[2]."/".$parts[3]."/";
                                    break;
                                default:
                                    # code...
                                    $path = "/controllers/";
                                    break;
                            }
                            //collect the last index of the array
                            $last = end($parts);

                            //grab the controller name and method call
                            $segments = explode('@',$last); 
                            //实例化控制器
                            require APP_PATH.$path.$segments[0].".php";
                            
                            //instanitate controller
                            $controller = new $segments[0]();

                            //call method and pass any extra parameters to the method
                            $controller->$segments[1](implode(",", $matched)); 
    
                            if (self::$halts) return;
                        } else {
                            call_user_func_array(self::$callbacks[$pos], $matched);
                            
                            if (self::$halts) return;
                        }
                        
                    }
                }
            $pos++;
            }
        }
 

        // run the error callback if the route was not found
        if ($found_route == false) {
            if (!self::$error_callback) {
                self::$error_callback = function() {
                    header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
                    echo '404';
                };
            }
            call_user_func(self::$error_callback);
        }
    }
}
