<?php
namespace Fubber\Templating;
use Fubber\Kernel;

/**
 * Factory to instantiate the template engine of choice. Allows for dependency
 * injection to be implemented.
 */
class TemplateFactory {
    
    /**
     * Create an instance of the template engine we're using.
     * 
     * @param string $template  Template name, without file extension. The template engine is expected to handle the file extension.
     * @param array $vars       Variables made available to the template engine
     * @return \Fubber\Templating\ITemplate
     */
    public static function create($template, array $vars=array()) {
        $kernel = Kernel::$instance;
        return call_user_func_array($kernel->config['templateFactory'], [$template, $vars]);
    }
}