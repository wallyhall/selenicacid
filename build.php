#!/usr/bin/env php
<?php
$me = basename($argv[0]);

$opts = getopt("a");

// **** phar.readonly must be disabled.  Attempt to fork and disable it if enabled.
if (ini_get("phar.readonly") === "1" && !array_key_exists("a", $opts)) {
    fwrite(STDOUT, <<<EOT
Cannot proceed with PHP's 'phar.readonly' INI setting enabled.
Attempting to auto-override...

EOT
    );
    
    if (!defined("PHP_BINARY")) {
        define("PHP_BINARY", $_SERVER['_']);
    }
    
    system(PHP_BINARY . " --define phar.readonly=off " . escapeshellarg(__FILE__) . " -a", $result);
    if ($result !== 0) {
        fwrite(STDERR, <<<EOT
        
Failed to auto-override the PHP 'phar.readonly' INI setting.

You should try overriding this option on the command-line yourself:
    php --define phar.readonly=off "$me"
    
Build process did not start.

EOT
        );
    }

    exit($result);

} elseif (ini_get("phar.readonly") === "1") {
    fwrite(STDERR, <<<EOT
    
** Auto-override of PHP phar.readonly INI setting failed **

EOT
    );
    exit(255);
}
// ****

fwrite(STDOUT, "Build starting...\n");

// PHP <5.3
if (!defined("__DIR__")) {
    define("__DIR__", dirname(__FILE__));
}

$src = realpath(__DIR__ . "/src/");
$out = realpath(__DIR__ . "/bin/selenicacid.phar");
$mods = realpath($src . "/lib/Modules/");

$phar = new Phar(
    $out,                // output PHAR filename
    0,                    // 
    "selenicacid.phar"    // internal PHAR reference name (phar:// ... /x.php)
);

fwrite(STDOUT, " * Packaging contents of '" . $src . "'\n");
$phar->buildFromDirectory($src);

fwrite(STDOUT, " * Compiling list of modules\n");
$modDirItr = new RecursiveDirectoryIterator($mods);
$recModItr = new RecursiveIteratorIterator($modDirItr);
$rgxModItr = new RegexIterator($recModItr, '~^' . $mods . '/(.*)Action([A-Z][a-zA-Z0-9]*)\.php$~', RegexIterator::REPLACE);
$rgxModItr->replacement = '$1$2';
$modulesList = iterator_to_array($rgxModItr, false);
foreach ($modulesList as $module) {
    fwrite(STDOUT, "    + " . $module . "\n");
}
$phar->addFromString("modules.json", json_encode($modulesList));

fwrite(STDOUT, " * Setting up PHAR stub\n");
$phar->setStub($phar->createDefaultStub('cli.php', 'httpRouter.php'));

fwrite(STDOUT, "Package built: '" . $out . "'\n");

