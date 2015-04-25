<?php

class Modules_ActionTest implements Modules_Interface
{
    public function methodGet($id, $params)
    {
        if ($id == "notfound") {
            throw new Modules_RouterException("Test 404 thrown", 404);
        }
    
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

    public function methodPut($id, $params, $data)
    {
        return $data;
    }

    public static function getDescription()
    {
        return "Test module, returns machine information and random GUID.";
    }
    
    public static function getUI()
    {
        /* Definition of the UI is based on the concept behind ClutterScript:
         * https://developer.gnome.org/clutter-cookbook/stable/script-ui.html
         *
         * Rather than directly returning JSON, we return a stack of PHP objects.
         * The Interface is responsible for deciding whether to convert to JSON or not.
         */
        
        return array("abc");
        /*$window = new UI_Window();
        
        
        
        $window->addGetQueryPage();
        $window->addGetResultsPage();
        $window->addPostPage();
        $window->addPutPage();
        $window->addDeletePage();
        
        return $window;*/
    }
}