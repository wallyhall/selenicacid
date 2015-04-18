<?php

class Modules_ActionIndex implements Modules_Interface
{
    public static function methodGet($params) {
        return array ("Welcome");
    }

    public static function methodPut($params)
    {
        return false;
    }

    public static function methodPost($params)
    {
        return false;
    }

    public static function methodDelete($params)
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
