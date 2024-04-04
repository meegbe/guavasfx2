<?php

//use FileRequest;

class File extends FileSystem
{
    public $name = null;
	public $size = null;
	public $tmp_name = null;
	public $type = null;
	public $extension = null;
	public $error = null;

    public function __construct($files = null)
    {
        //
    }

    public static function read($pathFile)
    {
        $pathFile = ROOT_DIR . str_replace(ROOT_DIR, '', $pathFile);
        if(is_readable($pathFile)) {
            return file($pathFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }
        return false;
    }

    public static function write($pathFile, $content = '', $replaceOrNew = true)
    {
        $pathFile = ROOT_DIR . str_replace(ROOT_DIR, '', $pathFile);
        $content = is_array($content) ? implode("\n", $content) : $content;
        if(is_writable($pathFile) || $replaceOrNew) {
            @file_put_contents($pathFile, $content);
        }
        return true;
    }

    public static function request($key)
    {
        if(array_key_exists($key, $_FILES)) {
            return new FileRequest($_FILES[$key]);
        }
        return false;
    }
}