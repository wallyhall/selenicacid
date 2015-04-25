<?php

class Cli_Interfaces_Http extends Cli_Interfaces_Abstract
{
    private function readHttpData(&$dataFinished)
    {
        $dataFinished = false;
        $dataLines = array();
        while (!$dataFinished && sizeof($dataLines) < 100 && !$this->dispatcher->ended()) {
            $buf = $this->dispatcher->in();
            if ($buf === false) {
                break;
            }
            
            $buf = trim($buf);
            if ($buf != "") {
                $dataLines[] = $buf;
            } else {
                $dataFinished = true;
            }
        }
        return $dataLines;
    }

    public function start()
    {
        $requestLines = $this->readHttpData($requestFinished);
        if (!$requestFinished) {
            $response = "HTTP/1.0 400 Bad request.\n\nBad request.";
            $this->dispatcher->error("Request was rejected ... size: " . sizeof($requestLines) . "\n");
            return false;
        }

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

        $data = null;            
        switch (strtolower($method)) {
            case "put":
            case "post":
                $dataLines = $this->readHttpData($dataFinished);
                if (!$dataFinished) {
                    $response = "HTTP/1.0 400 Bad request.\n\nBad request.";
                    $this->dispatcher->error("Data upload was rejected ... size: " . sizeof($dataLines) . "\n");
                }
                $data = json_decode(implode($dataLines));
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
                $output = Modules_Router::route($method, $path, $assocParams, $data, true);
                $response = "HTTP/1.0 200 OK\n\n" . $output;
            } catch (Modules_RouterException $e) {
                $response =
                    "HTTP/1.0 " . $e->getCode() . " " . $e->getMessage() . "\n\n" .
                    $e->getMessage();
            }
        }
        
        $this->dispatcher->out($response);
    }
}
