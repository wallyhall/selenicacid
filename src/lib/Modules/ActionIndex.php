<?php

class Modules_ActionIndex implements Modules_Interface
{
    public function methodGet($id, $params) {
        return array ("Welcome");
    }

    public function methodPut($id, $params, $data)
    {
        return false;
    }

    public function methodPost($params, $data)
    {
        return false;
    }

    public function methodDelete($id, $params)
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
