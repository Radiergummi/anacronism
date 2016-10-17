<?php
namespace Radiergummi\Anacronism\Modules;

/**
 * Writer interface.
 */
interface Writer
{
	/**
	 * write function.
	 * Writers need to implement the write method that writes the archive. it receives a list of files.
	 *
	 * @access public
	 * @param array $files
	 *
	 * @return mixed
	 */
	public function write(array $files);
}
