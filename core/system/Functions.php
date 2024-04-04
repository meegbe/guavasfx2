<?php

/**
 * Get Config By name
 * @param string $name
 * @return string|array $result
 */

function config($name) 
{
	$result = '';
	$config = [];
	$parts = explode('.', $name);
	if(is_array($parts) && sizeof($parts) > 1) {
		$fileConfig = GV_APP_DIR . 'config/' . $parts[0] . '.php';
		if(is_readable($fileConfig)) {
			array_shift($parts);
			$config = require $fileConfig;
			foreach ($parts as $index) {
				$config = $config[$index];
			}
			$result = $config;
		}
	}
	return $result;
}

function gv_encode($string)
{
	$salt_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$salt4chars = substr(str_shuffle($salt_chars), 0, 4);
	$string = substr_replace(base64_encode($string), $salt4chars, 1, 0);
	return $string;
	return base64_encode($string);
}

function gv_decode($string)
{
	$string = base64_decode($string);
	$string = substr($string, 12, strlen($string) - 28);
	return base64_decode($string);
}

function gv_encrypt($string, $key = 'gv')
{
	$salt = 'guavas-hash-key';
    $key = substr(hash('sha256', $salt . $key . $salt), 0, 32);
    $encryption_key = base64_decode($key);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($string, 'aes-256-cbc', $encryption_key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function gv_decrypt($string, $key = 'gv')
{
	$salt = 'guavas-hash-key';
    $key = substr(hash('sha256', $salt . $key . $salt), 0, 32);
    $encryption_key = base64_decode($key);
    @list($encrypted_data, $iv) = explode('::', base64_decode($string), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
}

function gv_debug()
{
	$backtrace = debug_backtrace();
	predump($backtrace);
}

function cleanXSS($data) 
{
	// Fix &entity\n;
	$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
	$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
	$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
	$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

	// Remove any attribute starting with "on" or xmlns
	$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

	// Remove javascript: and vbscript: protocols
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

	// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

	// Remove namespaced elements (we do not need them)
	$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

	do
	{
		// Remove really unwanted tags
		$old_data = $data;
		$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
	}
	while ($old_data !== $data);

	// return result
	return $data;
}

function gv_post($key, $default=null, $xss=false)
{
	if(isset(GV_INPUT_POST[$key])) {
		$input = GV_INPUT_POST[$key];
		if($xss && is_string($input)) {
			$input = cleanXSS($input);
		}
		return $input;
	}
	return $default;
}

function gv_get($key, $default=null, $xss=true) {
	if(isset(GV_INPUT_GET[$key])) {
		$input = GV_INPUT_GET[$key];
		if($xss && is_string($input)) {
			$input = cleanXSS($input);
		}
		return $input;
	}
	return $default;
}

function gv_request()
{

}

function gv_files()
{}

/**
 * This function displays structured information about one or more expressions that includes its type and value. 
 * Arrays and objects are explored recursively with values indented to show structure. 
 * 
 * @param 	mixed	$vars 	The expression to dump. 
 * @param 	boolean	$asJSON	Set true if return with content type json on browser
 * 
 * @return 	null	No value returned
 */
function predump($vars, $asJSON = true) 
{
	if($asJSON) {
		$json = json_encode($vars);
		header('Content-Type: application/json');
		echo $json;
	} else {
		echo "<pre>";
		var_dump($vars);
		echo "</pre>";
	}
	exit();
}

function server($name = '')
{
	if(empty($name)) {
		return $_SERVER;
	}
	return isset($_SERVER[$name]) ? $_SERVER[$name] : false;
}

function base_url($uri = '')
{
	return BASE_DIR . '/' . slashtrim($uri);
}

function make_dir($path, $permission = 0777, $recursive = false) 
{
	// create directory if not exist
    return is_dir($path) || mkdir($path, $permission, $recursive);
}
function make_file($path, $permission = 0644) 
{
	return @file_put_contents($path,"");
}
function remove_dir($directory, $recursive = true) 
{
	if(is_dir($directory)) {
		foreach(glob($directory."/*") as $file) {
			if(is_dir($file)) { 
				if($recursive) remove_dir($file);
			} else {
				unlink($file);
			}
		}
		rmdir($directory);
	}
	return false;
}

function slashtrim($string)
{
	return trim($string, '\\/');
}
function quotetrim($string)
{
	$string = trim($string, '"');
	$string = trim($string, "'");
	return $string;
}
function str2bool($value)
{
	return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}
function int2az($int, $char = '')
{
	$alphabet_num = 26;
	$N = floor($int / $alphabet_num);
	$DV = $int % $alphabet_num;
	if($DV === 0 && $N > 0) {
		$N--;
		$DV = $alphabet_num;
	}
	$ASCII = chr(64 + $DV);
	if($N > 0) {
		if($N > $alphabet_num) {
			int2az($N, $ASCII);
		}
		$ASCII = chr(64 + $N) . $ASCII;
	}
	
	return $char . $ASCII;
}
function slugify($string)
{
	$string = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
	$string = trim($string);
	$string = strtolower($string);
	$string = trim($string,'-');
	return $string;
}
function url($uri = '')
{
	$target = base_url() . ltrim($uri, '/');
	if (headers_sent()) {
		die("Redirect failed. Please click on this link: <a href='".$target."'>".$target."</a>");
	} else {
		exit(header("Location: ".$target));
	}
}
function format_size_units($bytes) {
	$i = floor(log($bytes) / log(1024));
	$sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

	return sprintf('%.02F', $bytes / pow(1024, $i)) * 1 . ' ' . $sizes[$i];
}

function between($num, $first, $last) {
	if($num >= $first && $num <= $last) {
		return true;
	}
	return false;
}

function gv_client_ip() {
	$ipaddress = '';
	if(isset($_SERVER['HTTP_CLIENT_IP']))
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if(isset($_SERVER['HTTP_X_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	else if(isset($_SERVER['HTTP_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	else if(isset($_SERVER['REMOTE_ADDR']))
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	else
		$ipaddress = 'UNKNOWN';
	return $ipaddress;
}

function get_user_agent() {
	if(isset($_SERVER['HTTP_USER_AGENT']))
	{
		return $_SERVER['HTTP_USER_AGENT'];
	}
	return 'Unknown';
}

/**
 * encode image
 */
function image_base64($fileUrl) 
{
	$type = pathinfo($fileUrl, PATHINFO_EXTENSION);
	$contentFile = file_get_contents($fileUrl);
  	$image = 'data:image/'.$type.';base64,'.base64_encode($contentFile);
  	return $image;
}

function csrf_create($expiry = CSRF_EXPIRY, $force = false) 
{
	$csrf = Session::get('csrf_session');
	if($csrf && ($csrf['timestamp'] > time()) && !$force) {
		return $csrf['token'];
	} else {
		$token = gv_encrypt(time() . uniqid(mt_rand(0,990), APP_KEY), APP_KEY);
		$csrf_session['token'] = $token;
		$csrf_session['timestamp'] = time() + $expiry;
		Session::set('csrf_session', $csrf_session);

		return $token;
	}
	return false;
}

function csrf_validate($token) 
{
	$result = false;
	$csrf_session = Session::get('csrf_session');
	if(isset($csrf_session['token'])) {
		if($csrf_session['timestamp'] > time()) {
			return $csrf_session['token'] == $token;
		}
		csrf_create(CSRF_EXPIRY, true);
	}
	return $result;
}

function error_handler( $errno, $errmsg, $filename, $linenum, $vars )
{
    // error was suppressed with the @-operator
    if (0 === error_reporting())
      return false;

    if ($errno !== E_ERROR)
      throw new \ErrorException( sprintf('%s: %s', $errno, $errmsg ), 0, $errno, $filename, $linenum );

}

if (!function_exists('getallheaders')) {
	function getallheaders() {
		$headers = [];
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
}