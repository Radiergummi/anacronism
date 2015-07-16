<?php
namespace Radiergummi\Cronos\Modules\Generators\Database;

/**
 * MysqlDump class.
 * 
 * @extends Database
 * @implements Module
 * @implements Generator
 */
class MysqlDump extends Database implements Generator
{
	/**
	 * dumpDatabase function.
	 * Creates a database dump using a shell command
	 *
	 * @example $dump = new MysqlDump('foo');
	 *
	 * @access private 
	 * @param string $database (default value: --all-databases)  the database to dump,
	 * defaulting to all databases
	 *
	 * @return void
	 */
	public function __construct($database = '--all-databases')
	{
		// return false if we have no shell access
		if (! static::checkShellAccess()) return false;
		
		// execute a mysqldump via commandline
		return shell_exec('mysqldump -u' . parent::$username . ' -p' . parent::$password . ' -h ' . parent::$host . ' ' . $database);
	}


	/**
	 * checkShellAccess function.
	 * checks wether shell access is disabled or not
	 * 
	 * @access public
	 * @return bool  wether shell access is available
	 */
	function checkShellAccess() {
    $disabled = explode(',', ini_get('disable_functions'));

		// if exec is within the disabled functions section of the php.ini, return false
    return ! in_array('exec', $disabled);
	}
}
