<?php 

class Hash
{
    public static function uuid() 
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public static function random($length, $char = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
    	$charLength = strlen($char);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
	        $randomString .= $char[rand(0, $charLength - 1)];
	    }
	    return $randomString;
    }

    public static function make($password)
    {
        $salt = defined('APP_KEY') ? APP_KEY : 'guavas-key';
        $md5password = md5($password);
        $sha1salt = sha1($salt);
        return base64_encode($md5password . $sha1salt);
    }

    public static function check($password, $correct_hash) {
        $salt = defined('APP_KEY') ? APP_KEY : 'guavas-key';
        $md5password = md5($password);
        $sha1salt = sha1($salt);
        $string = base64_encode($md5password . $sha1salt);
        return ($string === $correct_hash);
    }

    public static function file($file) {
        $salt = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $hash = hash_file('sha1', $file);
        $sha1salt = sha1($salt . $hash);
        return $sha1salt;
    }
}