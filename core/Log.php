<?php
/**
 * Logging
 *
 * PHP version 7.2
 */
class Log
{

    protected static function buildLog($type, $vars)
    {
        $datetime = date('Y-m-d H:i:s');
        if(!is_string($vars)) {
            ini_set('xdebug.overload_var_dump', 1);
            ob_start();
            var_export($vars);
            $vars = ob_get_clean();
            if (ob_get_contents()) ob_end_clean();
        }
        $vars = '[' . $datetime . '] '.$type.': ' . $vars . PHP_EOL;
        return $vars;
    }

    public static function info($vars, $vars2 = '')
    {
        $log = self::buildLog('INFO', $vars);
        file_put_contents(GV_LOG_ERROR, $log, FILE_APPEND | LOCK_EX);
        if(!empty($vars2)) {
            $log2 = self::buildLog('INFO', $vars2);
            file_put_contents(GV_LOG_ERROR, $log2, FILE_APPEND | LOCK_EX);
        }
        return false;
    }

    public static function error($vars, $vars2 = '')
    {
        $log = self::buildLog('ERROR', $vars);
        file_put_contents(GV_LOG_ERROR, $log, FILE_APPEND | LOCK_EX);
        if(!empty($vars2)) {
            $log2 = self::buildLog('ERROR', $vars2);
            file_put_contents(GV_LOG_ERROR, $log2, FILE_APPEND | LOCK_EX);
        }
        return false;
    }
}