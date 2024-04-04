<?php
/**
 * ErrorHandling Class for Guavas-Framework
 * @author      Roywae
 * @category    core
 * @package     ErrorHandling
 * @version     1.0.4
 */

class ErrorHandling {

    /* Error Handling Function */
    public static function addNotice($errNo, $errStr, $errFile, $errLine) {
        $error_msg = "Error Notice : " . $errNo . "\n";
        $error_msg .= "Message : " . $errStr . "\n";
        $error_msg .= "Location : " . $errFile . "\n";
        $error_msg .= "Line Number : " . $errLine . "\n";

        /* Error Logging in General error_log File*/
        error_log($error_msg, 0);
        exit;
    }

    public static function getError404() 
    {
        http_response_code(404);
        echo "<!DOCTYPE html><html><head><title>404 Page Not Found</title></head><body><center><h2>HTTP Error 404 - Page Not Found</h2><hr /><p>The page you are looking for doesn't exist or an other error occured.</p><p><a href=\"javascript: window.history.back()\">Go Back</a></p></center></body></html>";
        exit;
    }

    public static function getError403($message = "You don't have permission to access on this server") 
    {
        http_response_code(403);
        echo "<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><center><h2>HTTP Error 403 - Forbidden</h2><hr /><p>Forbidden: ".$message."</p></center></body></html>";
        exit;
    }
}
