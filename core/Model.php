<?php

class Model
{

	protected $_db = null;
	protected $_prefix = '';
	protected $tableName = '';
	protected $connection = null;
	protected $table = ''; 

	protected function mysqlDriver($options) 
	{
		$conn = [
			'host' => $options['host'],
            'username' => $options['username'], 
            'password' => $options['password'],
            'db'=> $options['db'],
            'port' => $options['port'],
            'prefix' => $options['prefix'],
            'charset' => $options['charset']
		];
		
		$this->_db = new MySQLiDB($conn);
		$this->_db->addConnection($this->connection, $conn);
		$this->_db->defConnectionName = $this->connection;
        $this->_db->connection($this->connection);
        $this->_db->connect($this->connection);
        $this->_db->setPrefix($conn['prefix']);
		$this->_db->setTableName($this->table);
		$this->_prefix = $conn['prefix'];
		return $this->_db;
	}

	public function __construct()
	{
		// construct
		$defConnection = config('connection.'.$this->connection);
        $this->mysqlDriver($defConnection);
	}

	public function __call($name, $args) 
	{
		$instance = $this->_db;
        if(method_exists($instance, $name)) {
            $result = call_user_func_array([$instance, $name], $args);
            return $result;
        } else {
            throw new \Exception("Method $name not found in Cache Object " . get_class($instance));
        }
    }

	public static function __callstatic($name, $args)
	{
		$ctrl = new static();
		$instance = $ctrl->_db;
		if(method_exists($instance, $name)) {
            $result = call_user_func_array([$instance, $name], $args);
            return $result;
        } else {
            throw new \Exception("Method $name not found in Cache Object " . get_class($instance));
        }
	}
}