<?php

class Modules_ActionTest
{
	static function run($params) {
		return array_merge(
			array(
				"hostname"=>php_uname('n'),
				"os"=>php_uname('s'),
				"php"=>PHP_VERSION,
				"datetime"=>date('Y-m-d H:i:s'),
				"rand"=>sha1(uniqid("", true) . php_uname() . rand(0, getrandmax()))
			),
			$params
		);
	}

}