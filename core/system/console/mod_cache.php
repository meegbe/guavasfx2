<?php

class mod_cache
{
    protected $args = [];
    protected $commandList = [
        'clear',
    ];

    public function __construct($args)
    {   
        $this->args = $args;
        $this->init();
    }

    protected function init()
    {
        if(!empty($this->args)) {
            $key = array_shift($this->args);
            if(in_array($key, $this->commandList)) {
                $methodName = $key . 'Method';
                $this->$methodName();
            }
        }
    }
    protected function clearMethod()
    {
        $name = '*';
		foreach($this->args as $ar) {
			if(preg_match('~--(name)=([\\w.]+)~', $ar, $matched)) {
				$var = (string)$matched[1];
				$$var = $matched[2];
			}
			if(preg_match('~--expiry-only~', $ar, $matched)) {
				Cache::clearExpiredOnly();
                echo "Cache cleared successfully.\n";
				return false;
			}
		}
		$num = Cache::clear($name);
        if($num) {
            echo "Cache cleared successfully.\n";
        }
    }

}