<?php
class RouterException extends Exception
{
}

class Modules_Router
{

    private static $modules = null;

    public static function loadModuleListFromFile($filename)
    {
        $moduleList = json_decode(file_get_contents($filename));
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

    public static function listModules()
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
                    $scorePos - strlen($moduleClass) + 1
                )
            );

        return $moduleClass;
    }
    
    public static function route($method, $module, $params, $json=false)
    {
        if (!in_array($module, self::$modules)) {
            throw new RouterException("Module not found.", 404);
        }
        
        $classMethod = "method" . ucwords(strtolower($method));
        switch ($classMethod) {
            case "methodPut":
            case "methodPost":
            case "methodDelete":
            case "methodGet":
                break;
            
            default:
                throw new RouterException("selenicacid does not support method.", 501);
        }
        
        $moduleClass = self::getModuleClass($module);
        
        if (!method_exists($moduleClass, $classMethod)) {
            throw new RouterException("Module does not implement method.", 405);
        }
        $result = $moduleClass::$classMethod($params);

        if ($json) {
            $jsonOpts = 0;
            // PHP >=5.4
            (defined("JSON_PRETTY_PRINT")) && $jsonOpts |= JSON_PRETTY_PRINT;
            $result = json_encode($result, $jsonOpts);
        }

        return $result;
    }


}
