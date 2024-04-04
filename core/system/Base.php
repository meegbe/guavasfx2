<?php

class Base
{

    protected $_driver = null;
    protected $version = 'unknown';
    protected $license = '';
    protected $expiry = '';

    protected $lcfile = '';
    protected $initfile = '';

    protected $_posts = [];
    protected $_gets = [];
    protected $_headers = [];

    public function __construct()
    {
        //
    }
    protected function getHeaders()
    {
        if (!function_exists('getallheaders')) {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            return $headers;
        }
        return getallheaders();
    }
    protected function globalInput()
    {
        $headers = $this->getHeaders();
        define('GV_INPUT_HEADERS', $headers);

        $input = [];
		if(isset($headers['Content-Type'])) {
			$contentType = strtolower($headers['Content-Type']);
			if(strpos($contentType, 'application/json') !== false) {
				$input = file_get_contents('php://input');
				$input = json_decode($input, true); 
			}
		}

        $inputGet = filter_input_array(INPUT_GET);
        $inputGet = is_array($inputGet) ? $inputGet : []; 
        $inputGet = array_merge((array)$inputGet, (array)$input);
		define('GV_INPUT_GET', $inputGet);

        $inputPost = filter_input_array(INPUT_POST);
        $inputPost = is_array($inputPost) ? $inputPost : []; 
        $inputPost = array_merge((array)$inputPost, (array)$input);
		define('GV_INPUT_POST', $inputPost);
    }
    protected function globalFunctions()
    {
        require GV_CORE_DIR . 'system/Functions.php';
    }
    protected function readLicense()
    {
        if(!is_readable($this->lcfile)) {
            echo "The system cannot be run. Your license is not recognized.";
            exit();
        }
        $content = $this->readGvFile($this->lcfile);
        list($version, $license, $expiry) = explode('|', $content);
        $this->version = $version;
        $this->license = $license;
        $this->expiry = $expiry;
        define('GV_SOFTWARE_VERSION', $version);
        define('GV_SOFTWARE_LICENSE', $license);
        define('GV_SOFTWARE_EXPIRY', $expiry);
        return $license;
    }
    protected function writeLicense($newLicense, $newExpiry)
    {
        if(is_readable($this->lcfile)) {
            $content = $this->readGvFile($this->lcfile);
            list($version, $license, $expiry) = explode('|', $content);
            $this->version = $version;
        }
        $this->license = $newLicense;
        $this->expiry = $newExpiry;
        $content = implode('|',[
            $this->version,
            $this->license,
            $this->expiry
        ]);
        $this->writeGvFile($this->lcfile, $content);
    }
    protected function readInit()
    {
        if(!is_readable($this->initfile)) {
            echo "The system cannot be run. Init Guavas file not found.";
            exit();
        }
        $content = $this->readGvFile($this->initfile);
        return unserialize($content);
    }

    /**
     * @param array $param
     */
    protected function writeInit($param = [])
    {
        $content = serialize($param);
        $this->writeGvFile($this->initfile, $content);
    }
    protected function readGvFile($file)
    {
        $content = @file_get_contents($file);
        return gv_decrypt($content);
    }
    protected function writeGvFile($file, $content)
    {
        $content = gv_encrypt($content);
        @file_put_contents($file, $content);
    }
}