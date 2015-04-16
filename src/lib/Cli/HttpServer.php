<?php

class Cli_HttpServer
{

	private static $shutdown = false;
	private static $sock = null;

	static function handleSignal($sig)
    {
		switch ($sig) {
            case SIGTERM:
            case SIGINT:
                self::$shutdown = true;
                fwrite(STDOUT, "** httpd: caught shutdown request, shutting down...\n");
				break;
		}
		
		return true;
	}

	static function start($user=null, $daemon=false)
	{
		fwrite(STDOUT, "HTTPD server starting...\n");
		
		if (PCNTL_ENABLED) {
			pcntl_signal(SIGTERM, array("Cli_HttpServer", "handleSignal"));
			pcntl_signal(SIGINT, array("Cli_HttpServer", "handleSignal"));
		}
	
		self::fork($user);
	}
	
	static function fork($user)
	{
		self::openPort();
		self::readCmd();
	}
	
	static function openPort()
	{
		if ((self::$sock = stream_socket_server("tcp://0.0.0.0:8080", $errno, $errstr)) === false) {
			fwrite(STDERR, "** httpd: stream_socket_server() failed: " . $errno . " - " . $errstr . "\n");
			exit(100);
		}
	}
	
	static function readCmd()
	{
        while (!self::$shutdown) {
            if (($conn = stream_socket_accept(self::$sock)) === false) {
                fwrite(STDERR, "Accept failed.\n");
                continue;
            }
            stream_set_blocking($conn, 0);

            // reference array for select()
            $connArray = array($conn);
            $emptyArray = array();

            $requestFinished = false;
            $requestLines = array();
            while (!self::$shutdown && !$requestFinished && sizeof($requestLines) < 100) {
                stream_select($connArray, $emptyArray, $emptyArray, 5);
				if (false === ($buf = fgets($conn))) {
					fwrite(STDERR, "Read failed.\n");
					break;
                }

				$buf = trim($buf);
				fwrite(STDOUT, "  << $buf\n");
				
				if ($buf != "") {
					$requestLines[] = $buf;
				} else {
					$requestFinished = true;
                }
			}

			if (self::$shutdown) {
				$response = "HTTP/1.0 500 Server shutting down.\n\nServer shutdown in progress.";
				fwrite(STDERR, "Request aborted due to pending shutdown.\n");
				
			} elseif (!$requestFinished) {
				$response = "HTTP/1.0 400 Bad request.\n\nBad request.";
				fwrite(STDERR, "Request was rejected ... size: " . sizeof($requestLines) . "\n");
				
			} else {
				// do stuff
				$rawPath = null;
				$rawParams = null;
				
				foreach ($requestLines as $requestLine) {
					if (preg_match("/^(GET|POST)\s+(\S+)\s.*$/", $requestLine, $requestLineParts) === 1) {
						$rawPath = parse_url($requestLineParts[2], PHP_URL_PATH);
						$rawParams = parse_url($requestLineParts[2], PHP_URL_QUERY);
						break;
					}
				}
				
				$response = "";
				if ($rawPath === null) {
					$response = "HTTP/1.0 400 Bad request.\n\nBad request.";
					fwrite(STDERR, "Request appears malformed.\n");
				} else {
					
					// URL HANDLING
					
					$module = substr(str_replace("/", "_", $rawPath), 1);
					if ($module === false) {
						$module = "Index";
					}
					
					if ($rawParams === null) {
						$assocParams = array();
                    } else {
                        parse_str($rawParams, $assocParams);
					}
					
					$output = Modules_Router::route($module, $assocParams, true);
					if ($output === false) {
						$response = "HTTP/1.0 404 Not Found.\n\nModule not found.";
					} else {
						$response = "HTTP/1.0 200 OK\n\n" . $output;
					}
				}
			}

            stream_select($emptyArray, $connArray, $emptyArray, 5);
			fwrite($conn, $response, strlen($response));
			fwrite(STDOUT, "  >> " . str_replace("\n", "\n  >> ", "$response\n"));

            fclose($conn);
        }
        fclose(self::$sock);
		
		if (!self::$shutdown) {
			fwrite(STDERR, "Adnormal exit.\n");
			exit(128);
		}
	}
	
	static function execCmd($cmd)
	{
	
	}


}

