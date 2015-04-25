<?php

abstract class Cli_Interfaces_Abstract
{
    public function setDispatcher(&$dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    abstract public function start();
}
