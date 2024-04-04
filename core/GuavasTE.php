<?php
/**
* Guavas v1.0
*
* Simple PHP templating engine
*
* @author Roy Wae <roywae@gmail.com>
* @license BSD 3-Clause License
*/

class GuavasTE {

	protected $_template = '';
    protected $_content = '';
    protected $_params = [];
    protected $_section = [];

    /**
	 * Object Constructor
     * @param string $path
     * @param array $param
     */
	public function __construct($path = null, $params = []) 
    {
		$this->_params = $params;

        // load content from file
		if(is_string($path)) {
			$this->loadFromFile($path);
		}
	}

    /**
    * Load template from file
    * @param string $path
    */
	public function loadFromFile($path) 
    {
		if(!is_readable($path)) {
            return false;
        }
        
		$this->_content = @file_get_contents($path);
    	return $this;
	}

    /**
    * Load template from string
    * @param string $string
    */
	public function loadFromString($string) 
    {
		$this->_content = $string;
    	return $this;
	}

    /**
    * Execute output Template
    * @return string $output
    */
	public function run() 
    {
		$this->extendParams();
		$this->extendTemplate();

		return $this->_template;
	}

    public function extendParams()
    {
        /** each loop handler */
        $eachpattern = '/\{@each\(([\s+\$\w.=>]+)\)\}(.*?)\{@endeach\}/s';
        $this->_content = preg_replace_callback($eachpattern, function (array $m) {
            array_shift($m);
            return $this->phpEach($m);
        }, $this->_content);

        /** if condition handler */
        $ifpattern = '/\{@if\(([\s+\$\w.<=>\+\-\"\'\]\[]+)\)\}(.*?)\{@endif\}/s';
        $this->_content = preg_replace_callback($ifpattern, function (array $m) {
            array_shift($m);
            $result = $this->phpIf($m, $this->_params);
            return $result;
        }, $this->_content);

        /** php script handler */
        $this->_content = preg_replace_callback("/\{@php\}(.*?)\{@endphp\}/s", function (array $m) {
            try {
                extract($this->_params);
                ob_start();
                eval($m[1]);
                $result = ob_get_contents();
                ob_end_clean();
                return $result;
            } catch(Throwable $e) {
                return "{Error: ".$e->getMessage().". In line ".$e->getLine()."}";
            }
        }, $this->_content);

        /** php var handler */
        $this->_content = preg_replace_callback("/\{(\\$[\\w.]+|[\\w.]+)\}/s", function (array $m) {  
            $string = $m[1];
            $string = $this->phpVars($string);
            $string = is_array($string) ? $this->errorNotif('Array to string conversion in '.$m[1]) : $string;
            return $string;
        }, $this->_content);

        /** function handler */
        $this->_content = preg_replace_callback("/\{@([\\w]+)\((.*?)\)\}/s", function (array $m) {
        	$method = $m[1].'Action';
        	return call_user_func([$this, $method], $m[2]);
        	
        }, $this->_content);
    }

    public function extendTemplate() 
    {
        /** layout file handler */
        $this->_content = preg_replace_callback( "/\@layout\((.*?)\)/s", function (array $m) {
			extract($this->_params);
            $file = quotetrim($m[1]); 
            $gvLayout =$this->loadViewTemplate($file);
            $this->_template = $gvLayout->run();
        }, $this->_content);

        /** include file handler */
        $this->_content = preg_replace_callback( "/\@include\((.*?)\)/s", function (array $m) {
            $str = quotetrim($m[1]);
            $target = 'app/views/'.str_replace('.', '/', $str) . '.php';
            $string = new GuavasTE(ROOT_DIR . $target, $this->_params);
            return $string->run();
        }, $this->_content); 
	
        /** section file handler */
		$this->_content = preg_replace_callback("/\@section\((.*?)\)(.*?)\@endsection/s", function (array $m) {
            $index = quotetrim($m[1]);
            $this->_section[$index] = $m[2];
        }, $this->_content);

        /** obtain file handler */
        $this->_template = preg_replace_callback("/\@obtain\((.*?)\)/s", function (array $m) {
            $index = quotetrim($m[1]);
            if(isset($this->_section[$index])) {
                return $this->_section[$index];
            } 
        }, $this->_template);

        $this->_template .= trim($this->_content); 
		return $this;
    }

    protected function loadViewTemplate($file, $path = GV_APP_DIR . 'views/') 
    {
		$file = str_replace('.', '/', $file) . '.php';
		$gvLayout = new GuavasTE($path . $file, $this->_params);
		return $gvLayout;
	}

    protected function errorNotif($msg)
    {
        if (0 === error_reporting())
            return false;
        return '{Error Notice: '.$msg.'}';
    }

    protected function phpVars($strVar)
    {
		extract($this->_params);
		if(strpos($strVar, '$') === 0) {
            $strVar = substr($strVar, 1);
            if(strpos($strVar,'.') !== false) {
                $vars = explode('.', $strVar);
                $key = reset($vars);

                if(!isset($$key)) {
                    return;
                }
                $strVar = is_object($$key) ? (array)$$key : $$key;
                array_shift($vars);
                foreach($vars as $var) {
                    $strVar = isset($strVar[$var]) ? $strVar[$var] : null;
                }
                $strVar = is_object($strVar) ? (array)$strVar : $strVar;
            } else {
                $strVar = isset($$strVar) ? $$strVar : null;
            }
			
		} else {
			$strVar = defined($strVar) ? constant($strVar) : '{'.$strVar.'}';
		}
		return $strVar;
    }

    protected function phpEach($matches)
    {
        $html = '';
        //extract($this->_params);
        list($option, $content) = $matches;
        $matched = preg_match('/(\$[\w\.]+)\s+as\s+([\$\w=>\s+]+)/i', $option, $m);
        if($matched) {
            $e = explode('=>', $m[2]);
            if(sizeof($e) == 1) {
                foreach($this->phpVars($m[1]) as $item) {
                    $data = $this->_params;
                    $data[substr($e[0],1)] = $item;
                    $gve = new GuavasTE(null, $data);
                    $gve->loadFromString($content);
                    $html .= $gve->run();
                }
            } else if(sizeof($e) == 2) {
                foreach($this->phpVars($m[1]) as $index=>$item) {
                    $data = $this->_params;
                    $data[substr($e[0],1)] = $index;
                    $data[substr($e[1],1)] = $item;
                    $gve = new GuavasTE(null, $data);
                    $gve->loadFromString($content);
                    $html .= $gve->run();
                }
            }
        }
		return $html;
    }

    protected function phpIf($content, $param, $counter = 0)
    {
        $html = '';
        extract($param);
        $strCode = 'return ' . $content[0] . ';';
        $result = eval($strCode);
        $newcontent = preg_split('/{@else(.*?)}/s', $content[1], 2);
        if($result) {
            $html = $newcontent[0];
        } else {
            if($counter > 10) {
                return '';
            }
            
            $subifpattern = '/\{@elseif\(([\s+\$\w.<=>\-\+]+)\)\}(.*)/s';
            $matched = preg_match($subifpattern, $content[1], $m);
            if($matched) {
                array_shift($m);
                $html = $this->phpIf($m, $param, ++$counter);
            } else {
                $arrStr = preg_split('/{@else}/s', $content[1]);
                if(sizeof($arrStr) > 1) {
                    return $arrStr[1];
                }
            }
        }
        return $html;
    }

    /** FUNCTION HANDLER */
    private function assetAction($string) 
    {
		$string = trim($string, "'\"");
		$string = trim($string, "/");
		return base_url('assets/' . $string);
	}

	private function urlAction($string) {
		$string = trim($string, "'\"");
		$string = trim($string, "/");
		return base_url() . $string;
	}

	private function number_formatAction($option) {
		$args = str_getcsv($option,",","'");
		$result = call_user_func_array('number_format', $args);
		return $result;
	}

	private function objectAction($string) {
		$result = $this->phpVars($string);
		return json_encode($result, JSON_FORCE_OBJECT);
	}

	private function base_urlAction($string) {
		$string = trim($string, "'\"");
		$string = trim($string, "/");
		return base_url($string);
	}

	private function csrf_createAction() {
		// call csrf_create() function
		return csrf_create();
	}

	private function csrf_formAction() {
		$csrf = csrf_create();
		return '<input type="hidden" name="_token" value="'.$csrf.'">';
	}

	private function serverAction($string) {
		$string = trim($string, "'\"");
		return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
	}

	private function uniqidAction() {
		// call uniqid() function
		return call_user_func('uniqid');
	}

	private function selectoptAction($option) {
		$args = str_getcsv($option,",","'");
		$options = $this->phpVars($args[0]);
		$opthtml = "";
		$default = "";
		if(isset($args[1])) {
			$args[1] = trim($args[1]);
			if(!empty($args[1])) {
				$default = $this->phpVars($args[1]);
			}
		}
		
		foreach($options as $key=>$opt) {
			$selected = $opt['name'] == $default ? ' selected' : ''; 
			$opthtml .= '<option value="'.$opt['name'].'"'.$selected.'>'.$opt['value'].'</option>';
		}
		return $opthtml;
	}

	private function predumpAction($vars) {
        $vars = $this->phpVars($vars);
        ini_set('xdebug.overload_var_dump',1);
        ob_start();
        predump($vars);
        $result = ob_get_clean();
        ob_end_clean();
        return $result;
	}
	
	private function floatvalAction($vars) {
		$vars = $this->phpVars($vars);
		return floatval($vars);
	}

	private function ucfirstAction($string) {
		$string = trim($string, "'\"");
		return call_user_func('ucfirst', $string);
	}

	private function ucwordsAction($string) {
		$string = trim($string, "'\"");
		return call_user_func('ucwords', $string);
	}

	private function strtolowerAction($string) {
		$string = trim($string, "'\"");
		return call_user_func('strtolower', $string);
	}

	private function strtoupperAction($string) {
		$string = trim($string, "'\"");
		return call_user_func('strtoupper', $string);
	}
	
	private function strtotimeAction($string) {
		$string = trim($string, "'\"");
		return call_user_func('strtotime', $string);
	}

	private function dateAction($string) {
		//$string = trim($string, "'\"");
		$args = str_getcsv($string,",","'");
		return call_user_func_array('date', $args);
	}

    /** FUNCTION HANDLER END */

}