<?php
/*/*        if (self::$shutdown) {
            $response = "HTTP/1.0 500 Server shutting down.\n\nServer shutdown in progress.";
            self::error("Request aborted due to pending shutdown.\n");
                
        } else*/
class Cli_TcpServer
{
    private static $shutdown = false;
    private static $sock = null;
    private static $interfaceClass = null;
    
    private static $timeout = 5;
    
    public static function dispatch($interfaceClass)
    {
        self::$interfaceClass = $interfaceClass;
    }

    public static function handleSignal($sig)
    {
        switch ($sig) {
            case SIGTERM:
            case SIGINT:
                self::$shutdown = true;
                fwrite(STDERR, "** caught shutdown request, shutting down...\n");
                break;
        }
        
        return true;
    }

    public static function start($ip, $port, $user=null, $daemon=false, $timeout=5)
    {
        if (PCNTL_ENABLED) {
            pcntl_signal(SIGTERM, array("Cli_TcpServer", "handleSignal"));
            pcntl_signal(SIGINT, array("Cli_TcpServer", "handleSignal"));
        }
    
        self::$timeout = $timeout;
    
        self::openPort($ip, $port);
        self::waitForConnection($timeout);
    }

    private static function forkConnection($conn)
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

        $interfaceClass = self::$interfaceClass;
        $interfaceClass::dispatchMethod($conn, $conn, self::$timeout);

        fclose($conn);

        if ($fork == 0) {
            // child exits
            exit(0);
        } else {
            return false;
        }
    }
    
    private static function openPort($ip, $port)
    {
        if ((self::$sock = stream_socket_server("tcp://" . $ip . ":" . $port, $errno, $errstr)) === false) {
            fwrite(STDERR, "** stream_socket_server() failed: " . $errno . " - " . $errstr . "\n");
            exit(100);
        }
        return true;
    }

    static public function waitForConnection()
    {
        while (!self::$shutdown) {
            if (($conn = stream_socket_accept(self::$sock)) === false) {
                fwrite(STDERR, "Accept failed.\n");
                continue;
            }
            stream_set_blocking($conn, 0);

            // Exit point for children.  Parent continues.
            // If fork fails or isn't available, this function will block.
            self::forkConnection($conn);
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

}
