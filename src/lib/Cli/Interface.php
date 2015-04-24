<?php

class Cli_Interface
{
    private static $shutdown = false;

    public static function handleSignal($sig)
    {
        switch ($sig) {
            case SIGTERM:
                self::$shutdown = true;
                break;
        }
        
        return true;
    }

    public static function start()
    {
        fwrite(STDOUT, <<<EOT
** INTERACTIVE MODE ENABLED **

Welcome to selenicacid.
You haven't specified any arguments so we're running in interactive mode.
Any command you run will return a JSON response as though it was an HTTP GET request.
You can pass the -h argument for instructions on running as an HTTP server.

Ctrl+D will exit this client.


EOT
        );
        
        if (PCNTL_ENABLED) {
            pcntl_signal(SIGTERM, array("Cli_Interface", "handleSignal"));
        }
    
        self::readCmd();
    }
    
    private static function readCmd()
    {
        while (!self::$shutdown && !feof(STDIN)) {
            fwrite(STDOUT, "selenicacid> ");
            $cmd = trim(fgets(STDIN));
            if ($cmd !== "") {
                self::execCmd($cmd);
            }
        }
        
        fwrite(STDOUT, "\n");
        exit(0);
    }
    
    private static function execCmd($cmd)
    {
        $params = preg_split("/\s+/", $cmd);
        $cmd = array_shift($params);
    
        switch ($cmd) {
            case "exit":
            case "quit":
            case "bye":
                self::$shutdown = true;
                break;
            
            case "help":
                fwrite(STDOUT, <<<EOT
Available commands:
    exit, quit, bye     Quit the interactive client
    help                Displays this help message
    list                Lists all modules available
    
RESTful commands (append module and parameters):
    put                 Create new object(s)
    delete              Delete existing object(s)
    get                 Get object(s)
    post                Update object(s)
    

EOT
                );
                break;
            
            case "list":
                $modules = Modules_Router::listModules();
                foreach ($modules as $module => $description) {
                    fwrite(
                        STDOUT,
                        "\n " . $module . "\n   " .    wordwrap($description, 72, "\n   ") . "\n"
                    );
                }
                fwrite(STDOUT, "\n");
                break;

            case "put":
            case "get":
            case "delete":
            case "post":
                $module = array_shift($params);
            
                $assocParams = array();
                foreach ($params as $param) {
                    list($k, $v) = array_merge(explode("=", $param, 2), array(true));
                    $assocParams[$k] = $v;
                }
                
                try {
                    $output = Modules_Router::route($cmd, $module, $assocParams, true);
                    fwrite(
                        STDOUT,
                        $output . "\n"
                    );
                } catch (RouterException $e) {
                    fwrite(STDERR, $e->getMessage() . "\n");
                }
                break;
            
            default:
                fwrite(STDERR, "Invalid command.\n");
        }
        
        return true;
    }
}

