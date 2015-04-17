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
	
        self::openPort($user);
        self::readCmd();
	}
	
	static function forkServer($user)
	{
    }

    static function forkRequest($conn)
    {
        $fork = -1;
        $childPid = "";
        if (PCNTL_ENABLED) {
            $fork = pcntl_fork();
            if ($fork == -1) {
                fwrite(STDERR, "Failed to fork().  Continuing single-threaded.");
            } elseif ($fork > 0) {
                // allow parent to continue
                fwrite(STDOUT, " -- Forked child #" . $fork . "\n");
                fclose($conn);
                return true;
            } else {
                fclose(self::$sock);
                $childPid = getmypid();
            }
        }

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
			fwrite(STDOUT, "  $childPid<< $buf\n");
				
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
				fwrite(STDERR, "Request appears malformed.\n");
			} else {
					
				// URL HANDLING
					
				$module = substr($rawPath, 1);
				if ($module === false) {
					$module = "Index";
				}
				
				if ($rawParams === null) {
					$assocParams = array();
                } else {
                    parse_str($rawParams, $assocParams);
				}
				
				try {
					$output = Modules_Router::route($method, $module, $assocParams, true);
					$response = "HTTP/1.0 200 OK\n\n" . $output;
				} catch (RouterException $e) {
					$response =
						"HTTP/1.0 " . $e->getCode() . " " . $e->getMessage() . "\n\n" .
						$e->getMessage();
				}
			}
		}

        stream_select($emptyArray, $connArray, $emptyArray, 5);
		fwrite($conn, $response, strlen($response));
        fwrite(STDOUT, "  $childPid>> " . str_replace("\n", "\n  $childPid>> ", "$response\n"));

        fclose($conn);

        if ($fork == 0) {
            // child exits
            exit(0);
        } else {
            return false;
        }
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

            // Exit point for children.  Parent continues.
            // If fork fails or isn't available, this function will block.
            self::forkRequest($conn);
        }

        if (PCNTL_ENABLED) {
            pcntl_wait($status);
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


