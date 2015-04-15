<?php

class Cli_HttpServer
{

	private static $shutdown = false;
	private static $sock = null;

	static function handleSignal($sig)
	{
		switch ($sig) {
			case SIGTERM:
				self::$shutdown = true;
				break;
		}
		
		return true;
	}

	static function start($user=null, $daemon=false)
	{
		fwrite(STDOUT, "HTTPD server starting...\n");
		
		if (PCNTL_ENABLED) {
			pcntl_signal(SIGTERM, array("Cli_HttpdServer", "handleSignal"));
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
		if ((self::$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
			fwrite(STDERR, "** httpd: socket_create() failed: " . socket_strerror(socket_last_error()) . "\n");
			exit(100);
		}
		
		if (!socket_set_option(self::$sock, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 5, 'usec' => 0))) {
			fwrite(STDERR, "** httpd: socket_set_option() failed: " . socket_strerror(socket_last_error(self::$sock)) . "\n");
			exit(103);		
		}

		if (socket_bind(self::$sock, "0.0.0.0", 8080) === false) {
			fwrite(STDERR, "** httpd: socket_bind() failed: " . socket_strerror(socket_last_error(self::$sock)) . "\n");
			exit(101);
		}

		// Allow backlog of 5 connections before refusing more
		if (socket_listen(self::$sock, 5) === false) {
			fwrite(STDERR, "** httpd: socket_listen() failed: " . socket_strerror(socket_last_error(self::$sock)) . "\n");
			exit(102);
		}
	}
	
	static function readCmd()
	{
		while (!self::$shutdown) {
			if (($msgsock = socket_accept(self::$sock)) === false) {
				fwrite(STDERR, "socket_accept() failed: reason: " . socket_strerror(socket_last_error(self::$sock)) . "\n");
				break;
			}

			$requestLines = array();
			$blankCount = 0;
			$requestFinished = false;
			while (!self::$shutdown && !$requestFinished && sizeof($request) < 100) {
				if (false === ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) {
					fwrite(STDERR, "socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n");
					break;
				}
				$buf = trim($buf);
				fwrite(STDOUT, "  << $buf\n");
				
				if ($buf != "") {
					$requestLines[] = $buf;
				} else {
					$blankCount++;
					if ($blankCount > 1) {
						$requestFinished = true;
					}
				}
			}
			
			if (self::$shutdown) {
				$response = "HTTP/1.0 500 Server shutting down.\n\nServer shutdown in progress.";
				fwrite(STDERR, "Request aborted due to pending shutdown.\n");
				
			} elseif (!$requestFinished) {
				$response = "HTTP/1.0 400 Bad request.\n\nBad request.";
				fwrite(STDERR, "Request was rejected ... size: " . sizeof($request) . "\n");
				
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
						$assocParams = array(); // ****
					}
					
					$output = Modules_Router::route($module, $assocParams);
					if ($output === false) {
						$response = "HTTP/1.0 404 Not Found.\n\nModule not found.";
					} else {
						$response = "HTTP/1.0 200 OK\n\n" . json_encode($output);
					}
				}
			}

			socket_write($msgsock, $response, strlen($response));
			fwrite(STDOUT, "  >> " . str_replace("\n", "\n  >> ", "$response\n"));

			socket_close($msgsock);
		}

		socket_close(self::$sock);
		
		if (!self::$shutdown) {
			fwrite(STDERR, "Adnormal exit.\n");
			exit(128);
		}
	}
	
	static function execCmd($cmd)
	{
	
	}


}


