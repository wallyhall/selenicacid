<?php

class Cli_Tty
{

    public static function handleSignal($sig)
    {
        switch ($sig) {
            case SIGTERM:
                // ******************
                self::$shutdown = true;
                fwrite(STDOUT, "\nCaught shutdown signal...\n");
                break;
        }
        
        return true;
    }


    public static function dispatch($interfaceClass)
    {
        if (PCNTL_ENABLED) {
            pcntl_signal(SIGTERM, array("Cli_TtyInterface", "handleSignal"));
        }

        /* Implementation of readCmd() may handle 1 or more requests, we don't care.
         * All we know is we're done when it returns.
         */
        $interfaceClass::dispatchMethod(STDIN, STDOUT);
    }

}
