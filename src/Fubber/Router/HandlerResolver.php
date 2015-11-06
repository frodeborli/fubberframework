<?php
namespace Fubber\Router;
use Fubber\Kernel;
use Phroute\Phroute\HandlerResolverInterface;

class HandlerResolver implements HandlerResolverInterface {
    public function resolve ($handler)
	{
		$kernel = Kernel::$instance;
		
	    if(is_callable($handler)) {
	        return $handler;
	    }
	    else if(is_string($handler)) {
	        // SomeController->someMethod will instantiate the controller
	        $info = explode("->", $handler);
	        
	        if(isset($kernel->instanceCache[$info[0]])) {
	        	$info = [$kernel->instanceCache[$info[0]], $info[1]];
	        } else if(class_exists($info[0])) {
	            $className = $info[0];
	            $kernel->instanceCache[$className] = new $className();
	            $info = [$kernel->instanceCache[$className], $info[1]];
	        }
	        if(is_callable($info))
	            return $info;
	    }
	    
	    throw new Exception("Unable to resolve handler '".$handler."'.");
	}
}