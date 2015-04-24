<?php

class Cli_Interfaces_Cli extends Cli_Interfaces_Abstract
{
    private static $shutdown = false;
    
    public static function start()
    {
        $fwriteReturn = self::out(<<<EOT
** INTERACTIVE MODE ENABLED **

Welcome to selenicacid.
You haven't specified any arguments so we're running in interactive mode.
Any command you run will return a JSON response as though it was an HTTP GET request.
You can pass the -h argument for instructions on running as an HTTP server.

Ctrl+D will exit this client.


EOT
        );
        
        self::readCmd();
        return true;
    }
        
    private static function readCmd()
    {
        while (!self::$shutdown && !self::streamEnded()) {
            self::out("selenicacid> ");
            $buf = self::in();
            if ($buf === false) {
                break;
            }
            
            $cmd = trim($buf);
            if ($cmd !== "") {
                self::execCmd($cmd);
            }
        }
        
        self::out("\n");
        return true;
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
                self::out(<<<EOT
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
                    self::out(
                        "\n " . $module . "\n   " .    wordwrap($description, 72, "\n   ") . "\n"
                    );
                }
                self::out("\n");
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
                    self::out($output . "\n");
                } catch (RouterException $e) {
                    self::out($e->getMessage() . "\n");
                }
                break;
            
            default:
                self::out("Invalid command.\n");
        }
        
        return true;
    }
}

