<?php

interface Modules_Interface
{
    public function methodGet($id, $params);
    /* ** Optional methods to be implemented as appropriate:
    public function methodPut($id, $params, $data);
    public function methodDelete($id, $params);
    public function methodPost($params, $data);
    */
    
    public static function getDescription();
    public static function getUI();
}

