<?php
namespace Radiergummi\Anacronism;

/**
 * Writer interface.
 */
interface Writer
{
	/**
	 * write function.
	 * Writers need to implement the write method that writes the archive. it receives the archive path.
	 *
	 * @access public
	 * @param string $archivePath
	 *
	 * @return mixed
	 */
	public function write(string $archivePath);
}
