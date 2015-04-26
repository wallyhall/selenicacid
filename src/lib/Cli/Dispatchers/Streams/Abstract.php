<?php

abstract class Cli_Dispatchers_Streams_Abstract extends Cli_Dispatchers_Abstract
{
    protected $shutdown = false;

    protected $streamArrayIn = null;
    protected $streamArrayOut = null;
    protected $emptyArray = array();
    
    protected $timeout = null;

    public function handleSignal($sig)
    {
        switch ($sig) {
            case SIGTERM:
            case SIGINT:
                $this->shutdown = true;
                fwrite(STDERR, "** caught shutdown request, shutting down...\n");
                break;
            
            case SIGCHLD:
                if (PCNTL_ENABLED) {
                    pcntl_wait($status, WNOHANG);
                }
                break;
        }
        
        return true;
    }

    public function dispatch(&$interface)
    {
        if (PCNTL_ENABLED) {
            pcntl_signal(SIGTERM, array($this, "handleSignal"));
            pcntl_signal(SIGINT, array($this, "handleSignal"));
            pcntl_signal(SIGCHLD, array($this, "handleSignal"));
        }

        parent::dispatch(&$interface);

        if (PCNTL_ENABLED) {
            pcntl_signal(SIGTERM, SIG_DFL);
            pcntl_signal(SIGINT, SIG_DFL);
            pcntl_signal(SIGCHLD, SIG_DFL);
        }
    }

    public function error($str)
    {
        fwrite(STDERR, $str);
    }

    public function ended()
    {
        return feof($this->streamArrayOut[0]) || $this->shutdown;
    }

    public function out($data)
    {
        stream_select($this->emptyArray, $this->streamArrayOut, $this->emptyArray, $this->timeout);
        fwrite($this->streamArrayOut[0], $data, strlen($data));
    }

    public function in()
    {
        @stream_select($this->streamArrayIn, $this->emptyArray, $this->emptyArray, $this->timeout);
        if (false === ($buf = fgets($this->streamArrayIn[0]))) {
            fwrite(STDERR, "Read failed.\n");
        }
        return $buf;
    }
}
