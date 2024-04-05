<?php
/**
 * Router Class for Guavas-Framework
 * @author      Roywae
 * @category    core
 * @package     Router
 * @version     2.0.0
 */
class Router
{
    protected $routes = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'OPTIONS'=> [],
        'PATCH'  => [],
        'DELETE' => [],
        'HEAD'   => []
    ];

    /**
     * @var array $patterns Pattern definitions for parameters of Route
     */
    protected $patterns = [
        ':any'      => '.*',
        ':id'       => '[0-9]+',
        ':prefix'   => '[a-z0-9\-\_]+',
        ':slug'     => '[a-zA-Z0-9\-]+',
        ':code'     => '[a-zA-Z0-9]+',
        ':name'     => '[a-zA-Z]+',
    ];
    const REGVAL = '/{(:.+?)}/';
    protected $middlewares = [];
    protected $params = [];
    public $passthru = false;
    public $fallback = false;

    protected $groups = [];

    public function __construct() 
    {
        //
    }

    protected function addRoute($method, $path, $handler)
    {
        // add prefix group
        $prefix = implode('/', $this->groups);
        $path = slashtrim(BASE_DIR . slashtrim($prefix . '/' . $path));

        // push to route list
        array_push($this->routes[$method], [
            'route' => $path,
            'handler' => $handler,
            'middlewares' => $this->middlewares,
        ]);
    }
    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
        return $this;
    }
    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
        return $this;
    }
    protected function stringHandler($handler, $args = [])
    {
        list($controller, $method) = array_pad(explode('@', $handler), 2, $handler);

        if(!class_exists($controller)) {
            echo "{Error Notice: Cannot calling ".$controller." class}";
            @exit;
        }
        $ctrl = new $controller;
        foreach($this->params as $key=>$par) {
            $ctrl->$key = $par;
        }
        
        if(!method_exists($ctrl, $method)) {
            echo "{Error Notice: Cannot calling ".$method." method}";
            @exit;
        }
        $result = call_user_func_array([$ctrl, $method], $args);
        return $result;
    }
    protected function objectHandler($handler, $args = [])
    {
        $result = call_user_func($handler, $args);
        return $result;
    }
    protected function runRouteCommand($command) 
    {
    	// output command
    	switch (gettype($command)) {
            case 'string':
            case 'integer':
            case 'double':
            case 'boolean':
                echo $command;
                break;
            case 'array':
            case 'object':
                header('Content-Type: application/json');
                echo json_encode($command);
                break;
            default:
                echo "{Error Notice: Unknown output format.}";
                break;
        }
    }
    protected function match($resource, $curRoute)
    {
        // search route 
        $matched    = false;
        $route      = $resource['route']; 
        $args 		= [];

        // checking route list
        $route = preg_replace('~(\\\\)+~', '',$route);
        $route = preg_replace('~(\/)+~', '\/', $route);
        $route = trim($route, '\/');
        $route = preg_replace_callback(static::REGVAL, function($matches) {
            return '(' . $this->patterns[$matches[1]] . ')';
        }, $route);
        $matched = preg_match_all("/^$route$/i", $curRoute, $matches);
        array_shift($matches);
        $args = array_column($matches, 0);

        return [$matched, $args, $route];
    }
    protected function middlewarePassthru($middleware) 
    {
        $this->passthru = true;
        $controller = 'App\Middleware\\'.$middleware;
        $class = new $controller;
        $nexthop = $class->handle($this);
        if(!$this->passthru) {
            $this->runRouteCommand($nexthop);
            @exit;
        }
    }
    public function dispatch() 
    {
        // find match routing
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == 'POST' 
            && str2bool(ENABLE_CSRF_VERIFY)
            && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {    
                
            if(is_null(gv_post('_token')) || !csrf_validate(gv_post('_token'))) {
                // display not found page
                http_response_code(403);
                ErrorHandling::getError403('CSRF tokens mismatch');
                exit();
            }
        }

        // find match routing
        foreach($this->routes[$method] as $resource) {
            // call match function
            list($matched, $args, $route) = $this->match($resource, CURRENT_ROUTE);
            if($matched) {
            	//middleware passthru
                if(!empty($resource['middlewares'])) {
                    //@$this->middlewarePassthru($resource['middlewares']);
                }
            	$handler = gettype($resource['handler']);
                $result = call_user_func_array([$this, $handler.'Handler'], [$resource['handler'], $args]);
                $this->runRouteCommand($result);
                return true;
            }
        }

        // display not found page
        http_response_code(404);
        ErrorHandling::getError404();
        return false;
    }
}