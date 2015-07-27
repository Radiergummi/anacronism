<?php
namespace Radiergummi\Anacronism\Modules;

/**
 * Exporter interface.
 */
interface Exporter
{
	/**
	 * add function.
	 * accepts a list of files to include in the archive
	 * 
	 * @access public
	 * @return void
	 */
	public function add($fileList);
	
	
	/**
	 * close function.
	 * closes the current handle and writes it to disk.
	 * 
	 * @access public
	 * @return void
	 */
	public function close();
}
