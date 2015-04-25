<?php

abstract class Cli_Dispatchers_Abstract
{
    abstract public function error($str);
    abstract public function ended();
    abstract public function out($data);
    abstract public function in();

    abstract protected function start();

    public function dispatch(&$interface)
    {
        $this->interface = &$interface;
        $this->interface->setDispatcher($this);
        
        $this->start();
    }

}
