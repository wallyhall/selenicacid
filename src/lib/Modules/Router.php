<?php
class Modules_Router
{
	static private $modules = array(
		"Test" => "A test module which returns server information and some random values.  Use for confirming selenicacid is running correctly.",
		"Index" => "Welcome message for pathless HTTP requests."
	);


	static function listAll()
	{
		return self::$modules;
	}
	
	
	static function route($module, $params, $json=false)
	{
		if (!array_key_exists($module, self::$modules)) {
			return false;
		}
	
		$scorePos = strrpos($module, '_');
		if ($scorePos !== false) {
			$scorePos = 0;
		}
		$moduleQualified = "Modules_" . substr($scorePos, 0, $scorePos) . "Action" . substr($module, $scorePos - strlen($module));
        $result = $moduleQualified::run($params);

        if ($json) {
            $jsonOpts = 0;
            // PHP >=5.4
            (defined("JSON_PRETTY_PRINT")) && $jsonOpts |= JSON_PRETTY_PRINT;
            $result = json_encode($result, $jsonOpts);
        }

        return $result;
	}


}
