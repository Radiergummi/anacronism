<?php
namespace Radiergummi\Anacronism\Modules\Exporters;

use \ZipArchive;

/**
 * Zip class.
 *
 * @implements \Radiergummi\Anacronism\Modules\Exporter
 */
class Zip implements \Radiergummi\Anacronism\Modules\Exporter
{
	/**
	 * archive
	 * the archive handle
	 * 
	 * (default value: null)
	 * 
	 * @var mixed
	 * @access private
	 */
	private $archive = null;
	
	
	/**
	 * basePath
	 * the path to the backup folder
	 * 
	 * (default value: '')
	 * 
	 * @var string
	 * @access private
	 */
	private $basePath = '';
	
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @param string $path			the path to store the archive in
	 * @param string $filename	the archives filename
	 * @return void
	 */
	public function __construct($path, $filename)
	{
		// create a trailing slash and cut any existing
		$this->basePath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		
		// create a new zip instance
		$this->archive = new X();
	}
	
	
	/**
	 * add function.
	 * 
	 * @access public
	 * @param mixed $files
	 * @return void
	 */
	public function add($files)
	{
		foreach ($files as $file) {

      // Get real and relative path for current file
      $filePath = $file->getRealPath();
      $relativePath = substr($filePath, strlen($this->basePath));

      // Add current file to archive
      $this->archive->addFile($filePath, $relativePath);
		}
	}
	
	
	/**
	 * close function.
	 * 
	 * @access public
	 * @return void
	 */
	public function close()
	{
		$this->archive->close();
	}
}
