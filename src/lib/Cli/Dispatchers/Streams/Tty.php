<?php

class Cli_Dispatchers_Streams_Tty extends Cli_Dispatchers_Streams_Abstract
{
    private $shutdown = false;

    public function handleSignal($sig)
    {
        switch ($sig) {
            case SIGTERM:
                // ******************
                $this->shutdown = true;
                fwrite(STDOUT, "\nCaught shutdown signal...\n");
                break;
        }
        
        return true;
    }


    protected function start()
    {
        if (PCNTL_ENABLED) {
//            pcntl_signal(SIGTERM, array("Cli_TtyInterface", "handleSignal"));
// ****** non-static?
        }
        
        $this->streamArrayIn = array(STDIN);
        $this->streamArrayOut = array(STDOUT);
        
        $this->interface->start();
    }

}
