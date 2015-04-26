<?php
/*/*        if ($this->shutdown) {
            $response = "HTTP/1.0 500 Server shutting down.\n\nServer shutdown in progress.";
            $this->error("Request aborted due to pending shutdown.\n");
                
        } else*/
class Cli_Dispatchers_Streams_TcpServer extends Cli_Dispatchers_Streams_Abstract
{
    private $sock = null;
    
    private $ip = '0.0.0.0';
    private $port = 10000;

    public function __construct($ip, $port, $timeout=5)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->timeout = $timeout;
    }

    protected function start()
    {
        $this->openPort($this->ip, $this->port);
        $this->waitForConnection($this->timeout);
    }

    private function forkConnection($conn)
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
                fclose($this->sock);
                $childPid = getmypid();
            }
        }

        $this->streamArrayIn = array($conn);
        $this->streamArrayOut = array($conn);

        $this->interface->start();

        fclose($conn);

        if ($fork == 0) {
            // child exits
            exit(0);
        } else {
            return false;
        }
    }
    
    private function openPort($ip, $port)
    {
        if (($this->sock = stream_socket_server("tcp://" . $ip . ":" . $port, $errno, $errstr)) === false) {
            fwrite(STDERR, "** stream_socket_server() failed: " . $errno . " - " . $errstr . "\n");
            exit(100);
        }
        return true;
    }

    public function waitForConnection()
    {
        while (!$this->shutdown) {
            if (($conn = @stream_socket_accept($this->sock)) === false) {
                fwrite(STDERR, "Accept failed.\n");
                continue;
            }
            stream_set_blocking($conn, 0);

            // Exit point for children.  Parent continues.
            // If fork fails or isn't available, this function will block.
            $this->forkConnection($conn);
        }

        if (PCNTL_ENABLED) {
            pcntl_wait($status);
        }
        fclose($this->sock);

        if (!$this->shutdown) {
            fwrite(STDERR, "Adnormal exit.\n");
            return false;
        }
    }

}
