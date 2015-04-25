<?php

class Cli_Interfaces_Cli_Test extends PHPUnit_Framework_TestCase
{
    private $capturedData = array();

    public function captureOut($data)
    {
        $this->capturedData[] = $data;
    }

    public function test()
    {
        Modules_Router::setModuleList(
            array(
                "Index"
            )
        );
    
        // new CLI interface
        $cli = new Cli_Interfaces_Cli();

        // stub for dispatcher (which will dispatch $cli)
        $dispatcher = $this->getMockForAbstractClass('Cli_Dispatchers_Abstract');
        $dispatcher
            ->method('in')
            ->will($this->onConsecutiveCalls('get Index', 'exit'));
        
        $dispatcher
            ->method('out')
            ->will($this->returnCallback(array($this, 'captureOut')));
        
        $dispatcher->dispatch($cli);
        $dispatcher->interface->start();

        /* Check the number of out() calls is what we expected.
         * Failure on this check implies the CLI interface is outright broken
         */
        $this->assertCount(
            5,
            $this->capturedData,
            "CLI interface returned unexpected number of responses"
        );
        
        $this->assertJsonStringEqualsJsonString(
            json_encode(array("Welcome")),
            $this->capturedData[2],
            "Unexpected result from 'get Welcome' command."
        );
    }

}
