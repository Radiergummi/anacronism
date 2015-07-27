<?php
namespace Radiergummi\Anacronism;

use \Radiergummi\Anacronism\Helpers\Hook,
		\Radiergummi\Anacronism\Helpers\Log;

/**
 * Backup class.
 *
 * @example
 * 	$backup = new Backup('backup-' . time(), 'zip'); // create new backup
 * 	$backup->folder('/var/www')->dbDump(); // add a folder and dump a db
 * 	$backup->store(['dropbox', 'onedrive'])->dispatch(); // store the backup on several locations, dispatch download
 */
class Backup
{
	/**
	 * path
	 * 
	 * (default value: '')
	 * 
	 * @var string
	 * @access public
	 * @static
	 */
	public static $path = '';
	
	
	/**
	 * the filename for the archive generated
	 *
	 * (default value: '')
	 *
	 * @var string
	 */
	private $archiveFilename = '';	


	/**
	 * list of generator calls
	 *
	 * (default value: null)
	 *
	 * @static
	 * @access private
	 * @var object
	 */
	private static $generators = null;


	/**
	 * the selected exporter
	 *
	 * (default value: null)
	 *
	 * @static
	 * @access private
	 * @var string
	 */
	private static $exporter = null;


	/**
	 * basePath
	 * the path to store all backup related files in
	 * 
	 * (default value: '')
	 * 
	 * @var string
	 * @access private
	 * @static
	 */
	private static $basePath = '';


	/**
	 * list of files to include in the backup
	 * 
	 * (default value: array())
	 * 
	 * @var array
	 * @access private
	 */
	private $fileList = array();


	/**
	 * __construct function.
	 * Creates a new backup instance
	 *
	 * @access public
	 * @param string $archiveFilename  	the name of the backup archive
	 * @param string $exporter  				the exporter to use
	 * @return void
	 */
	public function __construct($archiveFilename, $exporter)
	{
		// set the archive name to the parameter value
		$this->archiveFilename = $archiveFilename . '.' . $exporter;
		
		// set basepath to this files folder
		static::$path = dirname(__FILE__) . DIRECTORY_SEPARATOR;

		// load all generators
		if (is_null(static::$generators)) static::$generators = static::loadGenerators();

		// load the exporter
		if (is_null(static::$exporter)) static::$exporter = ucfirst(strtolower($exporter));
	}
	
	
	/**
	 * setBasePath function.
	 * sets the path to the backup output folder
	 * 
	 * @access public
	 * @param string $path
	 * @return void
	 */
	public function setBasePath($path)
	{
		if (realpath($path) == false) throw new Error('the given output path does not exist.');
		
		static::$basePath = realpath($path);
	}


	/**
	 * loadGenerators function.
	 * 
	 * @access private
	 * @static
	 * @return array	the available modules
	 */
	private static function loadGenerators()
	{
		$modules = array();
		
		// build the path to the generators folder
		$path = static::$path . 'Modules' . DIRECTORY_SEPARATOR . 'Generators';
		$dir  = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::CHILD_FIRST);
		
		// iterate over all generators
		foreach ($files as $file) {
			
			$module = substr($file->getFileName(), 0, -strlen('.' . $file->getExtension()));
			
			// add each class to the generators array
			$modules[$module] = 'Radiergummi\\Anacronism\\Modules\\generators\\' . $module . '::run()';
		}

		// return the list of generators
		return $modules;
	}
	
	

	
	/**
	 * buildHashFile function.
	 * 
	 * @access private
	 * @return void
	 */
	private function buildHashFile()
	{
		
	}
	
	
	/**
	 * store function.
	 * closes and stores the backup to all specified locations
	 * 
	 * @access public
	 * @param array $writers	the writers to write the backup to
	 * @return $this   				for chaining
	 */
	public function store($writers) {

		// build hashfile for the current stack
		$this->buildHashFile();
	
		// start the export
		$this->export();
		
		foreach ((array) $writers as $writer) {
			try {
				$this->write($writer);
			}
			
			// catch an eventual exception: we don't want the backup to fail silently because one
			// location is unusable. Ideally, another location will work anyway and we will get a 
			// notification for the failed process.
			catch (Exception $e) {
	
				// append any error to the logfile
				Log::append($e->getMessage());
				
				// proceed to the next writer
				continue;
			}
		}
		
		// return the object for chaining
		return $this;
	}
	
	
	/**
	 * write function.
	 * 
	 * @access private
	 * @param string $writer
	 * @return void
	 */
	private function write($writerName)
	{
		// build the writer class name
		$writerClass = 'Radiergummi\\Anacronism\\Modules\\Writers\\' . ucfirst(strtolower($writerName));

		// create an instance of the writer, which sets up a connection
		$writer = new $writerClass; 

		// run eventual hooks before starting to write
		Hook::trigger('beforeStartingWrite');

		// write the previously generated archive to the location
		$writer->write($this->archiveFilename);
		
		// run eventual hooks after writing
		Hook::trigger('afterWriting');
	}
	
	
	private function export()
	{
		// build the exporter class name
		$exporterClass = 'Radiergummi\\Anacronism\\Modules\\Exporters\\' . ucfirst(strtolower(static::$exporter));

		// create an instance of the exporter
		$archive = new $exporterClass(static::$basePath, $this->archiveFilename);

		// run eventual hooks before starting the export process
		Hook::trigger('beforeStartingExport', $archive);

		// add the current file list
		$archive->add($this->fileList);

		// run eventual hooks before finalizing the archive
		Hook::trigger('beforeClosingArchive', $archive);

		// finalize the archive
		$archive->close();

		// return this for chaining
		return $this;
	}



	/**
	 * __call function.
	 * Magic method to directly start generators on the instance
	 *
	 * @access public
	 * @param array $arguments  the arguments given with the generator
	 * @param string $method  	the generator
	 * @return bool  						the call status
	 */
	public function __call($method, $arguments)
	{
		if (in_array($method, get_class_methods($this))) return null;
		
		// format the generator name
		$generatorName = 'Radiergummi\\Anacronism\\Modules\\Generators\\' . ucfirst(strtolower($method));

		// create a new generator instance
		$generator = new $generatorName(static::$basePath, $arguments);

		try {
			// retrieve a list of files to include in the archive
			$files = call_user_func(array($generator, 'getFileList'));

			// add each file to the file list
			$this->fileList = array_merge($this->fileList, $files);
		}

		// catch an eventual exception
		catch (Exception $e) {

			// append this incidence to the logfile
			Log::append($e->getMessage());
		}

		// return object for chaining
		return $this;
	}
}
