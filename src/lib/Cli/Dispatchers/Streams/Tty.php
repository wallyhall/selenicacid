<?php

class Cli_Dispatchers_Streams_Tty extends Cli_Dispatchers_Streams_Abstract
{
    protected function start()
    {
        $this->streamArrayIn = array(STDIN);
        $this->streamArrayOut = array(STDOUT);
        
        $this->interface->start();
    }

}
