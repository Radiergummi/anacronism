<?php
namespace Radiergummi\Anacronism\Modules\Generators;

/**
 * Database class.
 * Base class for database generators
 */
abstract class Database
{
	/**
	 * instance
	 * 
	 * (default value: null)
	 * 
	 * @var mixed
	 * @access private
	 * @static
	 */
	private static $instance = null;
	
	
	/**
	 * instance function.
	 * 
	 * @access public
	 * @static
	 * @return object  the current database instance
	 */
	public static function instance()
	{
		// if there is no active connection, start one
		if (is_null(static::$instance)) static::$instance = static::connect();
		
		// return the database instance
		return static::$instance;
	}


	/**
	 * connect function.
	 * 
	 * @access public
	 * @static
	 * @return object  the PDO
	 */
	public static function connect()
	{
		return new PDO();
	}
}
