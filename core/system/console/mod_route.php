<?php

class mod_route
{
    protected $args = [];
    protected $commandList = [
        'info',
        'set',
        'license',
        'key'
    ];

    public function __construct($args)
    {   
        $this->args = $args;
        $this->init();
    }

    protected function init()
    {
        if(!empty($this->args)) {
            $routing = array_shift($this->args);
            list($ctrl, $method) = array_pad(explode('@', $routing), 2, 'index');
            $params = [];
            foreach($this->args as $par) {
                if(preg_match('~--([\\w\-]+)=([\\w.\-\_:]+)~', $par, $matched)) {
                    $var = (string)$matched[1];
                    $var = str_replace('-','_', $var);
                    $params[$var] = $matched[2];
                }
            }

            $ctrl = '\App\Controllers\\' . $ctrl;
            $ctrl = new $ctrl;
            if(method_exists($ctrl, $method)) {
                $result = call_user_func_array([$ctrl, $method], $params);
                if(is_string($result)) {
                    echo $result;
                }
            }
        }
        echo "For more information on a specific \"route\" command:";
		echo "\nphp guavas-cmd route [controller_name@method_name] [--option]";
		echo "\n\n";
    }

}