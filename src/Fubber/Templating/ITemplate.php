<?php
namespace Fubber\Templating;

interface ITemplate {
    /**
     * Construct a Template object, that renders the template located at
     * Kernel::$instance->config['views'].'/'.$path.'<some-extension>'.
     * 
     * @param string $path  The path to the template within the views root
     * @param array $vars   Variables that the template can use
     * @return ITemplate
     */
    public static function create($path, array $vars=array());
    
    /**
     * Create a Response object
     * 
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse();
}