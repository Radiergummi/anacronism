<?php
namespace Radiergummi\Anacronism\Modules\Writers;

use Radiergummi\Anacronism\Helpers\Log;

/**
 * Dummy class.
 */
class Dummy
{
	/**
	 * write function.
	 * writes an archive.
	 * 
	 * @access public
	 * @return void
	 */
	public function write($files)
	{
		echo '<pre>';
		echo var_export($files);
		echo '</pre>';
		Log::append('Dummy output to browser!', 1);
	}
}
