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
	
	
	static function route($module, $params)
	{
		if (!array_key_exists($module, self::$modules)) {
			return false;
		}
	
		$scorePos = strrpos($module, '_');
		if ($scorePos !== false) {
			$scorePos = 0;
		}
		$moduleQualified = "Modules_" . substr($scorePos, 0, $scorePos) . "Action" . substr($module, $scorePos - strlen($module));
		return $moduleQualified::run($params);
	}


}
