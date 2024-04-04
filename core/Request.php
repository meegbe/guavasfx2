<?php

class Request extends FileRequest
{
    public static function isAjaxRequest() 
    {
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {    
            return true;
        }
        return false;
    }
    public static function method()
    {
        if(isset($_SERVER['REQUEST_METHOD'])) {
			return strtoupper($_SERVER['REQUEST_METHOD']);
		}
        return false;
    }
    public static function all()
    {
        return $_REQUEST;
    }
}