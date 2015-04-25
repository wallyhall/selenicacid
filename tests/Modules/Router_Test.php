<?php

class Modules_Router_Test extends PHPUnit_Framework_TestCase
{
    // Tests the Modules/ActionTest class

    public function testRouteInvalidMethodException()
    {
        $this->setExpectedException("Modules_RouterException", "", 501);
        $result = Modules_router::route('invalidmethod', 'Test/', array(), null, false);
    }

    public function testRouteInvalidModuleException()
    {
        $this->setExpectedException("Modules_RouterException", "", 404);
        $result = Modules_router::route('get', 'invalidmodule/', array(), null, false);
    }
    
    public function testRouteModuleMethodNotSupportedException()
    {
        $this->setExpectedException("Modules_RouterException", "", 405);
        $result = Modules_router::route('delete', 'Test/', array(), null, false);
    }

    public function testRouteModuleIdNotFound()
    {
        $this->setExpectedException("Modules_RouterException", "", 404);
        $result = Modules_router::route('get', 'Test/notfound', array(), null, false);
    }
        
// ** post route

    public function testRouteModulePut()
    {
        $putData = array("abc" => "def");
        $result = Modules_router::route('put', 'Test/', array(), $putData, false);
        $this->assertEquals($putData, $result);
    }

    public function testGetModuleClass()
    {
        $className = Modules_Router::getModuleClass("Test");
        $this->assertEquals("Modules_ActionTest", $className);
    }

    public function testModuleProvidesMethod()
    {
        $methodProvided = Modules_Router::moduleProvidesMethod("Test", "get");
        $this->assertEquals(true, $methodProvided, "ActionTest should provide GET");
        
        $methodProvided = Modules_Router::moduleProvidesMethod("Test", "delete");
        $this->assertEquals(false, $methodProvided, "ActionTest should not provide DELETE");
    }
    
    public function testModuleLoading()
    {
        /* Confirm reading existing list (provided by unit test bootstrap)
         */
        $moduleList = Modules_Router::getModuleList();
        $this->assertNotEmpty($moduleList, "Module list didn't load during bootstrap?");

        /* Confirm it handles invalid path for JSON file
         */        
        $result = Modules_Router::loadModuleListFromFile("/nowherethatexists");
        $this->assertFalse($result, "Loading from disk failed to handle invalid list path.");
        
        /* Confirm it can handle reading a file
         */
        $modulesJsonHandle = tmpfile();
        $modulesJsonHandleMeta = stream_get_meta_data($modulesJsonHandle);
        $modulesJsonPath = $modulesJsonHandleMeta["uri"];
        
        $testModuleList = array("test123", "test345");

        // Create test file
        file_put_contents(
            $modulesJsonPath,
            json_encode($testModuleList)
        );
        
        $result = Modules_Router::loadModuleListFromFile($modulesJsonPath);
        $this->assertTrue($result, "Failed to load or parse the modules.json file (or json encoding failed?)");
        $testModuleListLoaded = Modules_Router::getModuleList();
        $this->assertEquals($testModuleList, $testModuleListLoaded, "Loaded test module list didn't verify.");
        
        /* Confirm it can handle invalid JSON formatting
         */
        file_put_contents(
            $modulesJsonPath,
            "invalid JSON content"
        );
        
        $result = Modules_Router::loadModuleListFromFile($modulesJsonPath);
        $this->assertFalse($result, "Failed to catch invalid formatting in modules JSON.");
                
        // Free temporary file
        fclose($modulesJsonHandle);

        /* Put everything back, and test setting via PHP array directly works too.
         */
        // Restore original list        
        $result = Modules_Router::setModuleList($moduleList);
        $this->assertTrue($result, "Failed to restore the original modules.json array (or was already corrupt?)");
        
        $restoredModuleList = Modules_Router::getModuleList();
        $this->assertEquals($moduleList, $restoredModuleList, "Restored module list does not match input.");
    }
    
    public function testGetUI()
    {
        $uiDescription = Modules_Router::getModuleUI("Test");
        $this->assertNotEmpty($uiDescription, "Test module should not have empty UI description");
    }

    public function testGetDescription()
    {
        $moduleDescription = Modules_Router::getModuleDescription("Test");
        $this->assertNotEmpty($moduleDescription, "Test module should not have empty description");
    }
    
    // ...
}
