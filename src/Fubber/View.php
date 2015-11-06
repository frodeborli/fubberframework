<?php
namespace Fubber;
use Fubber\Templating\TemplateFactory;

class View extends Response {
    public function __construct($template, array $vars=[]) {
        $template = TemplateFactory::create($template, $vars);
        $response = $template->getResponse();
        
        parent::__construct(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody(),
            '1.1',
            $response->getReasonPhrase());
    }
}