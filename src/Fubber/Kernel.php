<?php
namespace Fubber;
use \GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Psr7\Response;
use \Phroute\Phroute\Dispatcher;
use \Phroute\Phroute\RouteCollector;


/**
 * Class that will bootstrap the framework, parse the request and execute the appropriate controller according to the router.
 * The framework should run just as well within ReactPHP as within a traditional web server such as Apache or nginx.
 */
class Kernel {
    
    public $config;
    
    /**
     * Serve a request and exit
     */
    public static function serve(array $config) {
        $kernel = new Kernel($config);
        
        /**
         * getallheaders only exists within apache
         */
        if(function_exists('getallheaders')) {
            $headers = getallheaders();
        }
        else {
            $headers = [];
            foreach($_SERVER as $k => $v) {
                if(substr($k, 0, 5)==="HTTP_") {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($k, 5)))))] = $v;
                }
            }
        }
        
        $request = new Request(
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            $headers,
            file_get_contents('php://input'),
            substr($_SERVER["SERVER_PROTOCOL"], 5));

        $response = $kernel->handleRequest($request);
    }
    
    public function __construct(array $config) {
        $this->config = $config += [
            'views' => $config['root'].'/views',
            'config' => $config['root'].'/config',
            ];
    }
    
    public function getHandlerResolver() {
        return new Router\HandlerResolver();
    }
    
    public function getDispatcher() {
        static $dispatcher;
        if($dispatcher) return $dispatcher;
        $routes = include($this->config['config'].'/routes.php');
        
        //TODO: Check if routes have been cached
        if(true) {
            $collector = new RouteCollector();
            foreach($routes as $left => $right) {
                // $left is something like 'GET /'
                // $right is a callable
                $left = explode(" ", $left);
                if(sizeof($left) === 2) {
                    switch($left[0]) {
                        case 'GET':
                            $collector->get($left[1], $right);
                            break;
                        case 'POST':
                            $collector->post($left[1], $right);
                            break;
                        case 'ANY':
                            $collector->any($left[1], $right);
                            break;
                        case 'HEAD':
                            $collector->head($left[1], $right);
                            break;
                        case 'OPTIONS':
                            $collector->options($left[1], $right);
                            break;
                        case 'DELETE':
                            $collector->delete($left[1], $right);
                            break;
                        default:
                            throw new ConfigException("Don't understand the HTTP method '".$left[0]."' in routes.php.");
                    }
                }
            }
            $routeData = $collector->getData();
        }
        
        return $dispatcher = new Dispatcher($routeData, $this->getHandlerResolver());
    }
    
    /**
     * Handle a single request and return a Psr7\ResponseInterface
     * 
     * @param Request $request
     * @return Psr\Http\Message\ResponseInterface
     */
    public function handleRequest(Request $request) {
        $dispatcher = $this->getDispatcher();
        
        $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
   }
}