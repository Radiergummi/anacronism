<?php
namespace Radiergummi\Anacronism\Modules;

/**
 * Writer interface.
 */
interface Writer
{
	/**
	 * write function.
	 * writes an archive.
	 * 
	 * @access public
	 * @return void
	 */
	public function write($files);
}
