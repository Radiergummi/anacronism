<?php
namespace Radiergummi\Anacronism\Modules;

/**
 * Generator interface.
 */
interface Generator
{
	/**
	 * getFileList function.
	 * returns a list of files to include in the backup
	 * 
	 * @access public
	 * @return void
	 */
	public function getFileList();
}
