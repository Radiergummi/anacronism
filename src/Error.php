<?php 
namespace Radiergummi\Anacronism;

use \ErrorException;
use \Radiergummi\Anacronism\Helpers\Log;

/**
 * Error class.
 */
class Error extends \Exception
{
	/**
	 * exception function.
	 *
	 * Exception handler
	 *
	 * This will log the exception and output the exception properties
	 * formatted as html or a 500 response depending on your application config
	 * 
	 * @access public
	 * @static
	 * @param \Throwable	uncaught exception
	 * @return void
	 */
	public static function exception(\Throwable $e)
	{
		static::log($e);
		exit(1);
	}


	/**
	 * native function.
	 *
	 * Error handler
	 *
	 * This will catch the php native error and treat it as a exception
	 * which will provide a full back trace on all errors
	 * 
	 * @access public
	 * @static
	 * @param int $code
	 * @param string $message
	 * @param string $file
	 * @param int $line
	 * @param array $context
	 * @return void
	 */
	public static function native(int $code, string $message, string $file, int $line, array $context)
	{
		if ($code & error_reporting()) {
			static::exception(new ErrorException($message, $code, 0, $file, $line));
		}
	}


	/**
	 * shutdown function.
	 *
	 * Shutdown handler
	 *
	 * This will catch errors that are generated at the
	 * shutdown level of execution
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function shutdown()
	{
		if ($error = error_get_last()) {
			extract($error);

			static::exception(new ErrorException($message, $type, 0, $file, $line));
		}
	}


	/**
	 * log function.
	 * Log the exception depending on the application config
	 * 
	 * @access public
	 * @static
	 * @param \Throwable $throwable	The exception
	 * @return void
	 */
	public static function log(\Throwable $throwable)
	{
		Log::append($throwable->getMessage(), 2);
	}


	/**
	 * register function.
	 * registers the error handling functions
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function register()
	{		
		// Error handling
		set_exception_handler(array('Radiergummi\Anacronism\Error', 'exception'));
		set_error_handler(array('Radiergummi\Anacronism\Error', 'native'));
		register_shutdown_function(array('Radiergummi\Anacronism\Error', 'shutdown'));
	}
}
