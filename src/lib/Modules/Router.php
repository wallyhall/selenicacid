<?php

class Modules_Router
{

    private static $modules = null;

    public static function loadModuleListFromFile($filename)
    {
        if (
            !is_readable($filename) ||
            ($moduleListJson = file_get_contents($filename)) === false
        ) {
            return false;
        }
        
        $moduleList = json_decode($moduleListJson);
        if ($moduleList === null) {
            return false;
        }
        
        return self::setModuleList($moduleList);
    }

    public static function setModuleList($moduleList)
    {
        if (!is_array($moduleList)) {
            return false;
        }
        self::$modules = $moduleList;
        return true;
    }

    public static function getModuleList()
    {
        return self::$modules;
    }
    
    public static function moduleProvidesMethod($module, $method)
    {
        $classMethod = "method" . ucwords(strtolower($method));
        switch ($classMethod) {
            case "methodPut":
            case "methodPost":
            case "methodDelete":
            case "methodGet":
                $moduleClass = self::getModuleClass($module);
                return (method_exists($moduleClass, $classMethod));
            
            default:
                return false;
        }
    }
    
    public static function getModuleDescription($module)
    {
        $moduleClass = self::getModuleClass($module);
        return $moduleClass::getDescription();
    }
    
    public static function getModuleUI($module)
    {
        $moduleClass = self::getModuleClass($module);
        return $moduleClass::getUI();
    }
    
    public static function getModuleClass($module)
    {
        $scorePos = strrpos($module, '/');
        if ($scorePos === false) {
            $scorePos = -1;
        }
        $moduleClass =
            "Modules_" .
            str_replace("/", "_", 
                substr($module, 0, $scorePos + 1) .
                "Action" .
                substr(
                    $module,
                    $scorePos - strlen($module) + 1
                )
            );

        return $moduleClass;
    }
    
    public static function route($method, $path, $params, $data=null, $json=false)
    {
        $id = null;
        if (strlen($path) > 0) {
            $module = $path;
            if (substr($path, -1) == '/') {
                // strip tailing / - it's just a module URI
                $module = substr($path, 0, strlen($path) - 1);
            } else {
                // get module path
                $module = substr($path, 0, strrpos($path, '/'));
                // get object identifier
                $id = substr($path, strrpos($path, '/') + 1);
            }
        }

        if (!in_array($module, self::$modules)) {
            throw new Modules_RouterException("Module not found.", 404);
        }
        
        $classMethod = "method" . ucwords(strtolower($method));
        switch ($classMethod) {
            case "methodPut":
            case "methodPost":
            case "methodDelete":
            case "methodGet":
                break;
            
            default:
                throw new Modules_RouterException("selenicacid does not support method.", 501);
        }
        
        $moduleClass = self::getModuleClass($module);
        
        if (!method_exists($moduleClass, $classMethod)) {
            throw new Modules_RouterException("Module does not implement method.", 405);
        }
        $moduleObject = new $moduleClass;
        
        switch ($classMethod) {
            case "methodGet":
                $result = $moduleObject->methodGet($id, $params);
                break;
        
            case "methodDelete":
                $result = $moduleObject->methodDelete($id, $params);
                break;
                
            case "methodPut":
                $result = $moduleObject->methodPut($id, $params, $data);
                break;
                
            case "methodPost":
                $result = $moduleObject->methodPost($params, $data);
                break;
        }

        if ($json) {
            $jsonOpts = 0;
            // PHP >=5.4
            (defined("JSON_PRETTY_PRINT")) && $jsonOpts |= JSON_PRETTY_PRINT;
            $result = json_encode($result, $jsonOpts);
        }

        return $result;
    }


}
