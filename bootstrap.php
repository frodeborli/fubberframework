<?php
require("vendor/autoload.php");

$kernel = Fubber\Kernel::serve([
    "root" => __DIR__."/sample-app",
]);
