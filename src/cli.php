<?php
if (version_compare(PHP_VERSION, '5.2.0', '<')) {
    fwrite(STDERR, <<<EOT
I'm sorry but your version of PHP is too old to run selenicacid.
We've made every effort to make this software compatible with PHP 5.2.0 and above.
You will need to be running at least that version to proceed.

EOT
    );
    exit(250);
}

date_default_timezone_set(@date_default_timezone_get());

define("PCNTL_ENABLED", function_exists("pcntl_signal"));
if (!PCNTL_ENABLED) {
    fwrite(STDERR, <<<EOT

** Important warning:
     You're running PHP without the pcntl extensions.
     Without the pcntl extensions, you will not be able to daemonize the selenicacid
      httpd server or cleanly exit the interactive client.
     It is strongly recommended you run selenicacid on PHP with pcntl available.


EOT
    );
}

/* selenicacid's cli interface
 * 
 * If no args were passed, assume interactive CLI mode.
 * -s will start the httpd server (or try to use PHP's internal one)
 * -d will daemonize (with optional username change)
 * -p overrides the port
 * -l overrides the listening interface
 */

require_once("phar://selenicacid.phar/autoload.php");

declare(ticks = 1);

$opts = getopt("sd::p:l:");

if (!array_key_exists("s", $opts)) {
    Cli_Interface::start();
} else {
    Cli_HttpServer::start();
}
