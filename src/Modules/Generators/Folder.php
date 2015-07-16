<?php
namespace Radiergummi\Anacronism\Modules\Generators;

/**
 * Folder class.
 * 
 * @implements Module
 * @implements Generator
 */
class Folder implements Generator
{
	/**
	 * excludes
	 * 
	 * (default value: array())
	 * 
	 * @var array
	 * @access public
	 * @static
	 */
	public static $excludes = array();


	public function __construct()
	{
		
	}


	/**
	 * getDirectory function.
	 * recursively iterates over a directory
	 * 
	 * @access private
	 * @param mixed $path
	 * @return void
	 */
	private function getDirectory($path)
	{
		$files = array();
		
		$di = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
		foreach (new RecursiveIteratorIterator($di) as $name => $file) {

			// skip file if its a folder, they are being included anyway
			if ($excludeEmptyFolders && $file->isDir()) continue;

			
		}
		
		return $files;
	}
}
