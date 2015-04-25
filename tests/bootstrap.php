<?php
/* phpunit bootstrap */

date_default_timezone_set(@date_default_timezone_get());
define('PHPUNIT_RUNNING', 1);

require_once __DIR__ . "/../src/autoload.php";

Modules_Router::setModuleList(
    array(
        "Index",
        "Test"
    )
);
