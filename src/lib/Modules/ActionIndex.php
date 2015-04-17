<?php

class Modules_ActionIndex implements Modules_Interface
{
	static function methodGet($params) {
		return array ("Welcome");
	}

	static function methodPut($params)
	{
		return false;
	}

	static function methodPost($params)
	{
		return false;
	}

	static function methodDelete($params)
	{
		return false;
	}


	static function getDescription()
	{
		return "Test thing";
	}
	
	static function getUI()
	{
		return array();
	}
}