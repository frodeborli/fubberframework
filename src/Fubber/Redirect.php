<?php
namespace Fubber;

class Redirect extends Response {
    public function __construct($targetUri, $status=301) {
        parent::__construct($status, ["Location" => $targetUri]);
    }
}