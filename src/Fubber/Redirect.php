<?php
namespace Fubber;

class Redirect extends Response {
    /**
     * Cacheable redirect, but changes the protocol to GET if it is not already.
     */
    const MOVED_PERMANENTLY = 301;
    
    /**
     * Temporary redirect. Changes the protocol to GET if it is not already.
     */
    const TEMPORARY_REDIRECT = 307;
    
    /**
     * Cacheable redirect. Keeps protocol, so it allows POST-data to follow.
     */
    const PERMANENT_REDIRECT = 308;
    
    public function __construct($targetUri, $status=self::TEMPORARY_REDIRECT) {
        parent::__construct($status, ["Location" => $targetUri]);
    }
}