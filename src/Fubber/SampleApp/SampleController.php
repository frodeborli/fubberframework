<?php
namespace Fubber\SampleApp;
use Fubber\Controller;

class SampleController extends Controller {
    public function handler() {
        die("This was a non-static handler");
    }
    
    public static function staticHandler() {
        die("This was a static handler");
    }
}