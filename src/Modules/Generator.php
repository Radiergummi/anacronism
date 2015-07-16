<?php
namespace Radiergummi\Cronos\Modules;

/**
 * Generator interface.
 */
interface Generator
{
	/**
	 * buildFileList function.
	 * returns a list of files to include in the backup
	 * 
	 * @access public
	 * @return void
	 */
	public function buildFileList();
}
