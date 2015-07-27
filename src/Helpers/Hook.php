<?
namespace Radiergummi\Anacronism\Helpers;

/**
 * Hook class.
 */
class Hook
{
	/**
	 * listeners
	 * 
	 * (default value: array())
	 * 
	 * @var array
	 * @access public
	 * @static
	 */
	public static $hooks = array();


	/**
	 * bind function.
	 * binds a new hook
	 * 
	 * @access public
	 * @static
	 * @param string $name
	 * @param Callable $callback
	 * @return void
	 */
	public static function bind($name, $callback)
	{	
		static::$hooks[$name] = $callback;
	}
	
	/**
	 * remove function.
	 * removes an existing hook
	 * 
	 * @access public
	 * @static
	 * @param mixed $name
	 * @return void
	 */
	public static function remove($name)
	{
		if (static::$hooks[$name] !== false) {
			unset(static::$hooks[$name]);
		}
	}
	
	/**
	 * trigger function.
	 * triggers an event listener
	 * 
	 * @access public
	 * @static
	 * @param string $name
	 * @param array $arguments (default: array())	the arguments for this hook
	 * @return null|the callback response
	 */
	public static function trigger($name, $arguments = array())
	{
		// check wether the hook exists
		if (! isset(static::$hooks[$name])) return null;
		
		// call the registered callback for this listener
		return call_user_func_array(static::$hooks[$name], (array) $arguments);
	}
}
