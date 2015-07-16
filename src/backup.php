<?php
namespace Radiergummi\Cronos;

/**
 * Backup class.
 * 
 * Creates a backup for its parent folder, including multiple different
 * sources if specified.
 *
 * @author "Moritz Friedrich <moritz.friedrich@boxhorn-edv.com>"
 */
class Backup
{
	/**
	 * path
	 * the path to the current directory 
	 * 
	 * (default value: '')
	 * 
	 * @var string
	 * @access private
	 * @static
	 */
	private static $path = '';


	/**
	 * instance
	 * the current backup instance
	 * 
	 * (default value: null)
	 * 
	 * @var object
	 * @access private
	 * @static
	 */
	private static $instance = null;


	/**
	 * zipFile
	 * the current zip handle
	 * 
	 * (default value: null)
	 * 
	 * @var object
	 * @access private
	 */
	private $zipFile = null;


	/**
	 * create function.
	 * creates a new instance of the Backup class for chaining
	 * 
	 * @access public
	 * @static
	 * @param string $path (default: null)  the path to backup
	 * @return Backup  the current class instance
	 */
	public static function create($path = null)
	{
		static::$path = (is_null($path) ? dirname(__FILE__) . DIRECTORY_SEPARATOR : $path);

		// check if the instance exists already
		if (! isset(static::$instance)) static::$instance = new Backup();
		
		// return the instance
		return static::$instance;
	}


	/**
	 * __construct function.
	 * Constructs a new Backup
	 *
	 * @access private
	 * @return $this  the current object
	 */
	private function __construct()
	{
		// include database connection details
		require('globals.php');
		
		// dump the database
		$this->dumpDatabase($globalDBHost, $globalDBUser, $globalDBPass);
		
		// get a list of all files in the webroot
		$files = $this->getDirectory(static::$path);
		
		// create a new zip archive
		$this->zipFile = new ZipArchive();
		
		// set the filename and some options
		$this->zipFile->open('backup-' . date('Ymd-His') . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
		
		// iterate over files and add them to the archive
		foreach ($files as $file) {
			$this->zipFile->addFile(
				$file['path'],
				$file['relative']
			);
		}

		// return the object for chaining;
		return $this;
	}


	/**
	 * dispatch function.
	 * closes the file and starts the download
	 * 
	 * @access public
	 */
	public function dispatch()
	{
		// store the file name
		$filepath = $this->zipFile->filename;

		// close the zip file
		$this->zipFile->close();

		// set headers
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header('Content-type: application/zip');
		header("Content-Transfer-Encoding: Binary");
		header('Content-Disposition: attachment; filename="' . end(@explode('/', $filepath)) . '"');
		header("Content-Length: " . filesize($filepath)); 

		// stream the file to the client to avoid memory exhaustion
		$fp = @fopen($filepath, "rb");
		if ($fp) {
			while (!feof($fp)) {
				echo fread($fp, 8192);
				flush();

				if (connection_status() != 0) {
					@fclose($filepath);
					die();
				}
			}

			@fclose($filepath);
		}
		
		// delete the ZIP-file
		unlink($filepath);
		array_map('unlink', glob(static::$path . '*.sql'));

		exit;
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
			if ($file->isDir()) continue;

			$absolutePath = $file->getRealPath();
			$relativePath = substr($absolutePath, strlen(static::$path));
			
			$files[$name]['path'] = $absolutePath;
			$files[$name]['relative'] = $relativePath;
		}
		
		return $files;
	}
}


$b = Backup::create()->dispatch();
