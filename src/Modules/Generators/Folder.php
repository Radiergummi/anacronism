<?php
namespace Radiergummi\Anacronism\Modules\Generators;

use \RecursiveDirectoryIterator,
		\RecursiveIteratorIterator,
		\Radiergummi\Anacronism\Helpers\DirectoryFilter;
/**
 * Folder class.
 * 
 * @implements Generator
 */
class Folder implements \Radiergummi\Anacronism\Modules\Generator
{
	/**
	 * excludes
	 * files to explictly exclude from the backup
	 * 
	 * (default value: array())
	 * 
	 * @var array
	 * @access public
	 * @static
	 */
	public $excludes = array(
		'backup'
	);
	
	/**
	 * fileList
	 * the list of files to include in the backup
	 * 
	 * (default value: array())
	 * 
	 * @var array
	 * @access public
	 */
	public $fileList = array();
	
	
	/**
	 * basepath
	 * the base path
	 * 
	 * (default value: '')
	 * 
	 * @var string
	 * @access private
	 */
	private $basepath = '';

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @param string $path							the base path
	 * @param string|array $directories	the directories to include in the backup
	 * @param array $excludes						the files and folders to explictly exclude from the backup
	 * @return void
	 */
	public function __construct($basepath, $directories, $excludes = array())
	{
		// repeat for each given directory
		foreach ((array) $directories as $directory) {
			
			// merge existing file list with new directory
			$this->fileList = array_merge($this->fileList, $this->getDirectory($directory));
		}
	}
	
	
	/**
	 * getFileList function.
	 * 
	 * @access public
	 * @return array	the list of files to include in the backup
	 */
	public function getFileList()
	{
		// return this generators file list
		return $this->fileList;
	}


	/**
	 * getDirectory function.
	 * recursively iterates over a directory
	 * 
	 * @access private
	 * @param mixed $path	the path to search
	 * @return void
	 */
	private function getDirectory($path)
	{
		// create a list of files
		$files = array();
		
		// create a directory iterator, skipping current and parent directory
		$di = new RecursiveDirectoryIterator(realpath($path), RecursiveDirectoryIterator::SKIP_DOTS);
		
		// filter the results using the exclude array
		$filtered = new DirectoryFilter($di, $this->excludes);
		
		// iterate over filtered results, processing folders separately
		$iterator = new RecursiveIteratorIterator($filtered, RecursiveIteratorIterator::SELF_FIRST);

		// iterate over the directory
		foreach ($iterator as $name => $file) {

			// skip file if its a folder, they are being included anyway
			if ($file->isDir()) continue;
			
			$files[] = $file;
		}

		// return the file list
		return $files;
	}
}
