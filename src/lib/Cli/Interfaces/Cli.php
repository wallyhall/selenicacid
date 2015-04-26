<?php

class Cli_Interfaces_Cli extends Cli_Interfaces_Abstract
{
    private $shutdown = false;
    
    public function start()
    {
        $fwriteReturn = $this->dispatcher->out(<<<EOT
** INTERACTIVE MODE ENABLED **

Welcome to selenicacid.
You haven't specified any arguments so we're running in interactive mode.
Any command you run will return a JSON response as though it was an HTTP GET request.
You can pass the -h argument for instructions on running as an HTTP server.

Ctrl+D will exit this client.


EOT
        );
        
        $this->readCmd();
        return true;
    }
        
    private function readCmd()
    {
        while (!$this->dispatcher->ended()) {
            $this->dispatcher->out("selenicacid> ");
            $buf = $this->dispatcher->in();
            if ($buf === false) {
                break;
            }
            
            $cmd = trim($buf);
            if ($cmd !== "") {
                $this->execCmd($cmd);
            }
        }
        
        $this->dispatcher->out("\n");
        return true;
    }
    
    private function execCmd($cmd)
    {
        $params = preg_split("/\s+/", $cmd);
        $cmd = array_shift($params);
        $data = null;
        
        switch ($cmd) {
            case "exit":
            case "quit":
            case "bye":
                $this->shutdown = true;
                break;
            
            case "help":
                $this->dispatcher->out(<<<EOT
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
                $modules = Modules_Router::getModuleList();
                foreach ($modules as $module => $description) {
                    $this->dispatcher->out(
                        "\n " . $module . "\n   " .    wordwrap($description, 72, "\n   ") . "\n"
                    );
                }
                $this->dispatcher->out("\n");
                break;

            case "put":
            case "post":
                $data = "";
                while (!$this->shutdown && !$this->dispatcher->ended()) {
                    $this->dispatcher->out(" data> ");
                    $buf = $this->dispatcher->in();
                    if ($buf === false || trim($buf) == "") {
                        break;
                    }
                    $data .= $buf;
                }
                $data = json_decode($data);
                // no break, fall through instead
            
            case "get":
            case "delete":
                $module = array_shift($params);
            
                $assocParams = array();
                foreach ($params as $param) {
                    list($k, $v) = array_merge(explode("=", $param, 2), array(true));
                    $assocParams[$k] = $v;
                }
                
                try {
                    $output = Modules_Router::route($cmd, $module, $assocParams, $data, true);
                    $this->dispatcher->out($output . "\n");
                } catch (Modules_RouterException $e) {
                    $this->dispatcher->out($e->getMessage() . "\n");
                }
                break;
            
            default:
                $this->dispatcher->out("Invalid command.\n");
        }
        
        return true;
    }
}

