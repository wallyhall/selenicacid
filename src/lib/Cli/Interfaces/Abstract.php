<?php

abstract class Cli_Interfaces_Abstract
{
    private static $streamArrayIn = null;
    private static $streamArrayOut = null;
    private static $emptyArray = null;
    
    private static $timeout = 0;

    protected static function error($str)
    {
        fwrite(STDERR, $str);
    }

    protected static function streamEnded()
    {
        return feof(self::$streamArrayOut[0]);
    }

    protected static function out($data)
    {
        stream_select(self::$emptyArray, self::$streamArrayOut, self::$emptyArray, $timeout);
        fwrite(self::$streamArrayOut[0], $data, strlen($data));
    }

    protected static function in()
    {
        stream_select(self::$streamArrayIn, self::$emptyArray, self::$emptyArray, self::$timeout);
        if (false === ($buf = fgets(self::$streamArrayIn[0]))) {
            fwrite(STDERR, "Read failed.\n");
        }
        return $buf;
    }

    public static function dispatchMethod($streamIn, $streamOut, $timeout=0)
    {
        self::$timeout = $timeout;
    
        // reference array for select()
        self::$streamArrayIn = array($streamIn);
        self::$streamArrayOut = array($streamOut);
        self::$emptyArray = array();
    
        // Catch error writing to stdout stream (not setup properly?)
        if ($fwriteReturn === 0) {
            return false;
        }
    
        static::start();
    }

    abstract static function start();
}
