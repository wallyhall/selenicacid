<?php

abstract class Cli_Dispatchers_Streams_Abstract extends Cli_Dispatchers_Abstract
{
    protected $streamArrayIn = null;
    protected $streamArrayOut = null;
    protected $emptyArray = array();
    
    protected $timeout = null;

    public function error($str)
    {
        fwrite(STDERR, $str);
    }

    public function ended()
    {
        return feof($this->streamArrayOut[0]);
    }

    public function out($data)
    {
        stream_select($this->emptyArray, $this->streamArrayOut, $this->emptyArray, $this->timeout);
        fwrite($this->streamArrayOut[0], $data, strlen($data));
    }

    public function in()
    {
        stream_select($this->streamArrayIn, $this->emptyArray, $this->emptyArray, $this->timeout);
        if (false === ($buf = fgets($this->streamArrayIn[0]))) {
            fwrite(STDERR, "Read failed.\n");
        }
        return $buf;
    }
}
