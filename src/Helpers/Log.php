<?
namespace Radiergummi\Anacronism\Helpers;

/**
 * Log class.
 */
class Log
{
	/**
	 * location
	 * 
	 * (default value: '')
	 * 
	 * @var string
	 * @access public
	 * @static
	 */
	public static $location = '';
	
	
	/**
	 * loglevels
	 * the available severity levels for an entry
	 * 
	 * @var mixed
	 * @access public
	 * @static
	 */
	public static $loglevels = array(
		0 => 'info',
		1 => 'success',
		2 => 'error',
		3 => 'critical'
	);
	
	
	/**
	 * append function.
	 * appends a new entry to the logfile.
	 * 
	 * @example
	 *	{
	 *			"date": "04.05.2016 15:31:10",
	 *			"level": "critical",
	 *			"message": "the current exporter refused to write to a file."
	 *	}
	 * 
	 * @access public
	 * @static
	 * @param mixed $entry						the log entry
	 * @param int $level (default: 0)	the severity level for this entry
	 * @return void
	 */
	public static function append($entry, $level = 0)
	{
		// create a data block for this log entry, consisting of a date, the level and the log line.
		$data = array(
			'date' => date('d.m.Y H:i:s'),
			'level' => static::$loglevels[$level],
			'message' => $entry
		);
		
		// write the log entry to the logfile.
		// of no destination is set, this will default to the current folder
		file_put_contents(
			static::$location . 'backup.log',
			json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL,
			FILE_APPEND | LOCK_EX
		);
	}
	
	
	/**
	 * setLocation function.
	 * set the location for the backup log file
	 * 
	 * @access public
	 * @static
	 * @param string $path 	the path to the location where the logfile shall be stored
	 * @return void
	 */
	public static function setLocation($path)
	{
		// add a trailing slash, cut any existing
		static::$location = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	}
}
