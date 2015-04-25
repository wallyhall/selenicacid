<?php

class Cli_Interfaces_Http extends Cli_Interfaces_Abstract
{
    public function start()
    {
        $requestFinished = false;
        $requestLines = array();
        /** shutdown **/
        while (!$requestFinished && sizeof($requestLines) < 100 && !$this->dispatcher->ended()) {
            $buf = $this->dispatcher->in();
            if ($buf === false) {
                break;
            }
            
            $buf = trim($buf);
            if ($buf != "") {
                $requestLines[] = $buf;
            } else {
                $requestFinished = true;
            }
        }

        if (!$requestFinished) {
            $response = "HTTP/1.0 400 Bad request.\n\nBad request.";
            $this->dispatcher->error("Request was rejected ... size: " . sizeof($requestLines) . "\n");
                
        } else {
            // do stuff
            $rawPath = null;
            $rawParams = null;
                
            foreach ($requestLines as $requestLine) {
                if (preg_match("/^(GET|POST|PUT|DELETE)\s+(\S+)\s.*$/", $requestLine, $requestLineParts) === 1) {
                    $rawPath = parse_url($requestLineParts[2], PHP_URL_PATH);
                    $rawParams = parse_url($requestLineParts[2], PHP_URL_QUERY);
                    $method = $requestLineParts[1];
                    break;
                }
            }
                
            $response = "";
            if ($rawPath === null) {
                $response = "HTTP/1.0 400 Bad request.\n\nBad request.";
                $this->dispatcher->error("Request appears malformed.\n");
            } else {
                    
                // URL HANDLING
                    
                $path = substr($rawPath, 1);
                if ($path === false) {
                    $path = "Index/";
                }
                
                if ($rawParams === null) {
                    $assocParams = array();
                } else {
                    parse_str($rawParams, $assocParams);
                }
                
                try {
                    $output = Modules_Router::route($method, $path, $assocParams, true);
                    $response = "HTTP/1.0 200 OK\n\n" . $output;
                } catch (RouterException $e) {
                    $response =
                        "HTTP/1.0 " . $e->getCode() . " " . $e->getMessage() . "\n\n" .
                        $e->getMessage();
                }
            }
        }
        
        $this->dispatcher->out($response);
    }
}
