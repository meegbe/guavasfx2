<?php

class mod_http
{
    protected $args = [];

    public function __construct($args)
    {   
        $this->args = $args;
        $this->init();
    }

    public function init()
    {
        $port = '8080';
		$ip = '127.0.0.1';
		$dir = 'public_html';
		$forceindex = false;
        foreach($this->args as $ar)
		{
			if(preg_match('~--(port|ip|dir|force\-index)=([\\w.]+)~', $ar, $matched))
			{
				$var = (string)str_replace('-','', $matched[1]);
				$$var = $matched[2];
			}
		}
		$dir = ROOT_DIR . $dir;
		$indexfile = $forceindex ? $dir . '/index.php' : '';
		$command = 'php -S ' . $ip . ':' . $port . ' '.$indexfile.' -t ' . $dir; 
		echo "Built-in Web Server Started.\n";
		$pid = shell_exec($command);
    }
}