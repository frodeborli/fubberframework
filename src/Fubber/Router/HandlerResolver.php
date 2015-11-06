<?php
namespace Fubber\Router;
use \Phroute\Phroute\HandlerResolverInterface;

class HandlerResolver implements HandlerResolverInterface {
    public function resolve ($handler)
	{
	    if(is_callable($handler)) {
	        die("A");
	        return $handler;
	    }
	    else if(is_string($handler)) {
	        // SomeController->someMethod will instantiate the controller
	        $info = explode("->", $handler);
	        if(class_exists($info[0])) {
	            $className = $info[0];
	            $info = [new $className(), $info[1]];
	        }
	        if(is_callable($info))
	            return $info;
            var_dump($info);
	        die("OK");
	    }
	    
	    throw new Exception("Unable to resolve handler '".$handler."'.");
	}
}