<?php
/**
 * Form Validation
 *
 * PHP version 7.2
 */
class FormValidation
{

	protected $_inputs = array();
	protected $_errors = array();
	protected $_roles = array();
	protected $patterns = [
		'required',
		'int',
		'float',
		'string',
		'alphanum',
		'email',
		'length', 		// with value		
		'max',			// with value
		'min',			// with value
		'date'			// with format value
	];

	public function __construct($formInput = [])
	{
		$this->_inputs = $formInput;
		$this->_errors = array();
	}

	public function validate($roles = []) 
	{
		$this->_roles = $roles;
		$isValid = $this->doCheck();
		return $isValid;
	}

	public function hasError()
	{
		if(!empty($this->_errors)) {
			return true;
		}
		return false;
	}

	public function getErrors($asString = false)
	{
		return $asString ? implode(', ', $this->_errors) : $this->_errors; 
	}

	protected function doCheck()
	{
		foreach($this->_roles as $key=>$role)
		{
			$conditions = explode('|', $role);
			$valid = $this->roleCheck($key, $conditions);
		}
	}

	protected function roleCheck($key, $conditions = [])
	{
		$result = true;
		foreach($conditions as $method)
		{
			list($method, $value) = array_pad(explode(':', $method), 2, '');
			if(in_array($method, $this->patterns)) {
				call_user_func_array([$this, 'is_'.$method], [$key, $value]);
			}
		}
		return $result;
	}

	protected function is_required($key)
	{
		$input = $this->_inputs;
		if(!isset($input[$key])) {
			$this->_errors[] = 'Field ' . $key . ' is required';
			return false;
		}	
		return true;
	}

	protected function is_int($key)
	{
		$input = $this->_inputs;
		if(isset($input[$key]) && filter_var($input[$key], FILTER_VALIDATE_INT) === false) {
			$this->_errors[] = 'Field ' . $key . ' must INT type';
		}
		return true;
	}

	protected function is_float($key)
	{
		$input = $this->_inputs;
		if(isset($input[$key]) && filter_var($input[$key], FILTER_VALIDATE_FLOAT) === false) {
			$this->_errors[] = 'Field ' . $key . ' must FLOAT type';
		}
		return true;
	}

	protected function is_string($key)
	{
		$input = $this->_inputs;
		if(isset($input[$key]) && !is_string($input[$key])) {
			$this->_errors[] = 'Field ' . $key . ' must STRING type';
		}
		return true;
	}

	/**
     * Validation for email format
     *
     * @param mixed $key
     * @return boolean
     */
    protected function is_email($key){
    	$input = $this->_inputs;
        if(filter_var($input[$key], FILTER_VALIDATE_EMAIL) === false) {
        	$this->_errors[] = 'Field ' . $key . ' is invalid email';
        }
        return true;
    }

    protected function is_length($key, $value)
	{
		$input = $this->_inputs;
		if(isset($input[$key]) && (strlen($input[$key]) == (int)$value)) {
			$this->_errors[] = 'Field ' . $key . ' length must '.$value.' characters';
		}
		return true;
	}

	protected function is_min($key, $value)
	{
		$input = $this->_inputs;
		if(isset($input[$key]) && (strlen($input[$key]) < (int)$value)) {
			$this->_errors[] = 'Field ' . $key . ' min length is '.$value.' characters';
		}
		return true;
	}

	protected function is_max($key, $value)
	{
		$input = $this->_inputs;
		if(isset($input[$key]) && (strlen($input[$key]) > (int)$value)) {
			$this->_errors[] = 'Field ' . $key . ' max length is '.$value.' characters';
		}
		return true;
	}
}