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
     Without the pcntl extensions, you will not be able to daemonize the
     selenicacid server or cleanly exit the interactive client.
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

require_once(__DIR__ . "/autoload.php");
if (!Modules_Router::loadModuleListFromFile(__DIR__ . "/../../modules.json")) {
    die("\nUnable to read modules.json which implies the build process failed.\n");
}

declare(ticks = 1);

$opts = getopt("std::p:l:");

/* Static classes and methods are used here to avoid reader confusion:
 * We'll never have more than one interface running at a time, and some interfaces
 * utilise forking - we don't need objects floating around in memory unused in children.
 */
$interface = false;

$result = false;
if (!array_key_exists("s", $opts) && !array_key_exists("t", $opts)) {
    $cli = new Cli_Interfaces_Cli();
    $tty = new Cli_Dispatchers_Streams_Tty();
    $result = $tty->dispatch($cli);
    
} elseif (array_key_exists("t", $opts)) {
    fwrite(STDOUT, "telnet server starting...\n");
    $cli = new Cli_Interfaces_Cli();
    $tcp = new Cli_Dispatchers_Streams_TcpServer('0.0.0.0', 10000);
    $result = $tcp->dispatch($cli);
    
} else {
    fwrite(STDOUT, "httpd server starting...\n");
    $http = new Cli_Interfaces_Http();
    $tcp = new Cli_Dispatchers_Streams_TcpServer('0.0.0.0', 8080);
    $result = $tcp->dispatch($http);
}

if ($result) {
    exit(0);
} else {
    exit(1);
}
