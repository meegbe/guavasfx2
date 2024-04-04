<?php
/**
 * ErrorHandling Class for Guavas-Framework
 * @author      Roywae
 * @category    core
 * @package     View
 * @version     2.0.0
 */
class View
{

	/**
	 * Fullpath view file location
	 *
	 * @var	string
	 */
	const PATH_VIEW = GV_APP_DIR . 'views/';

	public function __construct()
	{
		// init default views
	}

	public static function __callstatic($name, $args) 
	{
		$ctrl = new static;
        if(method_exists($ctrl, $name)) {
            $result = call_user_func_array([$ctrl, $name], $args);
            return $result;
        } else {
            throw new \Exception("Method $name not found in Cache Object " . get_class($ctrl));
        }
	}

	/**
     * Render a view file
     *
     * @param string $view  The view file
     * @param array $args  Associative array of data to display in the view (optional)
     *
     * @return void
     */
	protected static function render($viewName, $args = [], $gve_enabled = true)
	{
		$viewName = str_replace('.', '/', $viewName) . '.php';
		$viewFile = static::PATH_VIEW . slashtrim($viewName);

		// check file exists
		if(!is_readable($viewFile)) { 
			return "{Error: File ".$viewName." not found.}"; 
		}

		// build view with Guavas Template Engine
		if($gve_enabled) {
			$gve = new \GuavasTE($viewFile, $args);
			return $gve->run();
		}

		extract($args, EXTR_SKIP);
		require $viewFile;
		return false;
	}

}