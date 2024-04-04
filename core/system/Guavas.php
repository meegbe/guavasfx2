<?php
//namespace Core\System;

class Guavas extends Base
{
    protected $version = 'unknown';
    protected $license = '';
    protected $expiry = '';

    protected $_args = null;

    const INITFILE = GV_CORE_DIR . 'system/libs/init.gv';
    const LCFILE = GV_CORE_DIR . 'system/libs/license.gv';

    public function __construct($argv = null)
    {
        parent::__construct();
        $this->_args = $argv;
    }
    public function initialize()
    {
        $this->autoload();
        $this->globalInput();
        $this->globalFunctions();
        $this->lcfile = static::LCFILE;
        $this->initfile = static::INITFILE;
        define('WEB_MODE', 'web');
        define('CLI_MODE', 'console');

        $driver = new \Core\System\Driver;
        $driver->initSoftware();
        $driver->initHost();
    }
    public function run($method = 'web')
    {
        $this->validLicense();
        $this->readInit();
        $this->readLicense();
        $this->buildInitDefine();

        if(ACCESS_CONTROL_ALLOW_ORIGIN) {
			header("Access-Control-Allow-Origin: " . ACCESS_CONTROL_ALLOW_ORIGIN);
		}
        if($method == WEB_MODE) {
            make_dir(ROOT_DIR . GV_SESSION_DIR, 0755, true);
            ini_set('session.save_path', ROOT_DIR . GV_SESSION_DIR);
            $this->webMethod();
        } elseif($method == CLI_MODE) {
            $this->consoleMethod();
        }
        
    }

    public function buildInitDefine()
    {
        $param = $this->readInit();
        $fileCfgSystem = GV_APP_DIR . 'config/system.php';
		if(is_readable($fileCfgSystem)) {
			$cfgSystem = require $fileCfgSystem;
            foreach($cfgSystem as $key=>$value) {
                $param[$key] = $value;
            }
            $this->writeInit($param);
		}

        foreach($param as $key=>$value) {
            define($key, $value);
        }
    }

    protected function autoload()
    {
        // init autoload
        require GV_CORE_DIR . 'system/Autoload.php';

        $autoload = new Autoload;
        $autoload->addNamespace('App\Controllers', 'app/controllers');
		$autoload->addNamespace('App\Middleware', 'app/middlewares');
		$autoload->addNamespace('App\Repositories', 'app/repositories');
		$autoload->addNamespace('App\Models', 'app/models');
		$autoload->addNamespace('App\Libs', 'app/libs');
		$autoload->addNamespace('Core', 'core');
		$autoload->app(true);
    }

    protected function webMethod()
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        $currRoute = explode('?', $requestUri);
		$currRoute = array_shift($currRoute);
		define('CURRENT_ROUTE', slashtrim($currRoute));

        // init session start
		if (session_status() == PHP_SESSION_NONE) {
			session_name(GV_SESSION_NAME);
		    session_start();
		}

        // init log
		ini_set('error_log', GV_LOG_ERROR); 
		ini_set('display_errors', GV_ENVI != 'production');
		date_default_timezone_set(DEFAULT_TIMEZONE);
        
        // call router
        $app = new Router;
        foreach(glob(GV_APP_DIR . 'routes/*.php') as $file){
		    require $file;
		}
        $app->fallback = false;
        $app->dispatch();
    }

    protected function consoleMethod()
    {
        $commandList = [
            'system',
            'http',
            'cache',
            'route',
            'help'
        ];
        if(is_array($this->_args) && count($this->_args) > 1) {
            array_shift($this->_args);
			$moduleName = $this->_args[0];
			if(in_array($moduleName, $commandList)) {
				array_shift($this->_args);
                require GV_CORE_DIR . 'system/console/mod_' . $moduleName . '.php';
                $className = 'mod_' . $moduleName;
                new $className($this->_args);
				return true;
			}	
		}
        print_r($this->_args);
		echo "For more information on a specific command, type \"help\" command-name\n";
    }

    protected function buildKey()
    {
        $key = \Hash::uuid();



        return $key;
    }

    protected function validLicense()
    {
        if(!empty($this->expiry)) {
            if(strtotime($this->expiry) < time()) {
                echo "Your license has expired!";
                exit();
            }
        }
        return true;
    }


}