<?php

interface Modules_Interface
{
    public function methodGet($params);
    /* ** Optional methods to be implemented as appropriate:
    public function methodPut($params);
    public function methodPost($params);
    public function methodDelete($params);
    */
    
    public static function getDescription();
    public static function getUI();
}

