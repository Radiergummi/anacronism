<?php 
namespace Radiergummi\Anacronism;

use \ErrorException;
use \Radiergummi\Anacronism\Helpers\Log;

/**
 * Error class.
 */
class Error
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
	 * @param object	The uncaught exception
	 * @return void
	 */
	public static function exception($e)
	{
		static::log($e);

		if ('development' == 'development') {
			// clear output buffer
			while(ob_get_level() > 1) ob_end_clean();

				echo '<html>
					<head>
						<title>Uncaught Exception</title>
						<style>
							body{font-family:"Roboto",arial,sans-serif;background:#fff;color:#333;margin:2em}
							article{margin:2rem;background:#eee;border-radius:3px;padding-bottom:1rem;}
							article>:not(h1){padding:.5rem 1rem}
							h2,h3,p{margin:0}
							h1{margin:0 0 .5rem;padding:.5rem;font-weight:normal;text-shadow:1px 1px 1px rgba(0,0,0,.1);font-size:1.5rem;border-bottom:1px solid rgba(0,0,0,.05);border-radius: 3px 3px 0 0;color:#fff;text-align:center;}
							h1.e1,h1.e16,h1.e64,h1.e256{background:#FB3000;}
							h1.e2,h1.e32,h1.e128,h1.e512,h1.e4096{background:#F27456;}
							h1.e4{background:#F2B256;}
							h1.e8,h1.e1024,h1.e2048,h1.e8192{background:#DF56F2;}
							code{background:#D1E751;border-radius:4px;padding:2px 6px;white-space:pre-line}
							pre{margin:0 1rem;padding:.5rem;background:rgba(0,0,0,.05);border-radius:3px;font-size:1.1rem}
						</style>
					</head>
					<body>
						<article>
							<h1 class="e' . $e->getCode() . '">Uncaught Exception</h1>
							<p><code>' . $e->getMessage() . '</code></p>
							<h3>Origin</h3>
							<p><code>' . substr($e->getFile(), strlen(PATH)) . ' on line ' . $e->getLine() . '</code></p>
							<h3>Trace</h3>
							<pre>' . $e->getTraceAsString() . '</pre>
						</article>
					</body>
					</html>';
		} else {
			// issue a 500 response
			echo 'interal server error.';
		}

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
	public static function native($code, $message, $file, $line, $context)
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
	 *
	 * Exception logger
	 *
	 * Log the exception depending on the application config
	 * 
	 * @access public
	 * @static
	 * @param object $e	The exception
	 * @return void
	 */
	public static function log($e)
	{
		Log::append($e->getMessage(), 2);
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
