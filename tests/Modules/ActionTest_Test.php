<?php

class Modules_ActionTest_Test extends PHPUnit_Framework_TestCase
{
    // Tests the Modules/ActionTest class

    public function testGet()
    {
        $actionTest = new Modules_ActionTest();
    
        $a = $actionTest->methodGet(
            null,
            array(
                "testInput"=>"abc",
                "anotherTestInput"=>"def"
            )
        );
        
        $this->assertNotEmpty($a);
        $this->assertEquals("abc", $a["testInput"]);
        $this->assertEquals("def", $a["anotherTestInput"]);
    }

    

    // ...
}
