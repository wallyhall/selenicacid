<?php

class Modules_ActionIndex implements Modules_Interface
{
    public function methodGet($params) {
        return array ("Welcome");
    }

    public function methodPut($params)
    {
        return false;
    }

    public function methodPost($params)
    {
        return false;
    }

    public function methodDelete($params)
    {
        return false;
    }


    public static function getDescription()
    {
        return "Test thing";
    }
    
    public static function getUI()
    {
        return array();
    }
}
