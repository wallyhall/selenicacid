<?php

interface Modules_Interface
{
    public static function methodGet($params);
    /* ** Optional methods to be implemented as appropriate:
    public static function methodPut($params);
    public static function methodPost($params);
    public static function methodDelete($params);
    */
    
    public static function getDescription();
    public static function getUI();
}

