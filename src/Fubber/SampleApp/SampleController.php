<?php
namespace Fubber\SampleApp;
use Fubber\Controller;
use Fubber\View;
use Fubber\Redirect;

class SampleController extends Controller {
    public static function front() {
        return new Redirect('/random');
    }
    
    public function random() {
        return new View('random-number', ["random" => mt_rand(0,99)]);
    }
}