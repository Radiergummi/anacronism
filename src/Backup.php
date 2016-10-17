<?php
	namespace Radiergummi\Anacronism;

	use Radiergummi\Anacronism\Events\ExportEvent;
	use Radiergummi\Anacronism\Events\WriteEvent;
	use Radiergummi\Anacronism\Helpers\Log;
	use Symfony\Component\EventDispatcher\EventDispatcher;

	/**
	 * Backup class.
	 *
	 * @example
	 *    $backup = new Backup('backup-' . time(), 'zip'); // create new backup
	 *    $backup->folder('/var/www')->dbDump(); // add a folder and dump a db
	 *    $backup->store(['dropbox', 'onedrive'])->dispatch(); // store the backup on several locations, dispatch
	 *    download
	 */
	class Backup
	{
		/**
		 * the event dispatcher
		 *
		 * @var \Symfony\Component\EventDispatcher\EventDispatcher
		 */
		private $events;

		/**
		 * the filename for the archive generated
		 * (default value: '')
		 *
		 * @var string
		 */
		private $archiveFilename = '';

		/**
		 * list of generator calls
		 * (default value: null)
		 *
		 * @access private
		 * @var object
		 */
		private $generators = null;

		/**
		 * the selected exporter
		 * (default value: null)
		 *
		 * @access private
		 * @var string
		 */
		private $exporter = null;

		/**
		 * basePath
		 * the path to store all backup related files in
		 * (default value: '')
		 *
		 * @var string
		 * @access private
		 */
		private $basePath = '';

		/**
		 * list of files to include in the backup
		 * (default value: array())
		 *
		 * @var array
		 * @access private
		 */
		private $fileList = [ ];

		/**
		 * __construct function.
		 * Creates a new backup instance
		 *
		 * @access public
		 * @param string $archiveFilename the name of the backup archive
		 * @param string $exporter        the exporter to use
		 */
		public function __construct(string $archiveFilename, string $exporter)
		{
			// set the archive name to the parameter value
			$this->archiveFilename = $archiveFilename . '.' . $exporter;

			// set base path to this files folder
			$this->setBasePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'temp');

			// load all generators
			if (is_null($this->generators)) {
				$this->generators = $this->loadGenerators();
			}

			// load the exporter
			if (is_null($this->exporter)) {
				$this->exporter = ucfirst(strtolower($exporter));
			}

			$this->events = new EventDispatcher();
		}

		/**
		 * setBasePath function.
		 * sets the path to the backup output folder
		 *
		 * @access public
		 * @param string $path
		 * @return void
		 * @throws Error
		 */
		public function setBasePath(string $path)
		{
			if (! realpath($path)) {
				mkdir($path);
			}

			$this->basePath = realpath($path) . DIRECTORY_SEPARATOR;
		}

		/**
		 * loadGenerators function.
		 *
		 * @access private
		 * @return array    the available modules
		 */
		private function loadGenerators(): array
		{
			$modules = [ ];

			// build the path to the generators folder
			$path  = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Generators';
			$dir   = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
			$files = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::CHILD_FIRST);

			// iterate over all generators
			foreach ($files as $file) {
				if ($file->isDir()) {
					continue;
				}

				$module = $file->getBaseName('.php');

				// add each class to the generators array
				$modules[ $module ] = 'Radiergummi\\Anacronism\\Modules\\Generators\\' . $module . '::run()';
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
			$hashTable = [ ];

			foreach ($this->fileList as $filePath) {
				$file = new \SplFileInfo($filePath);

				// hash the file path, size and CTime using murmur
				$hashTable[ $file->getRealPath() ] = murmurhash3(
					$file->getRealPath() .
					$file->getSize() .
					$file->getCTime()
				);
			}

			file_put_contents($this->basePath . $this->archiveFilename . '.hash.json', json_encode($hashTable));
		}

		/**
		 * store function.
		 * closes and stores the backup to all specified locations
		 *
		 * @access public
		 * @param array $writers the writers to write the backup to
		 * @return Backup        for chaining
		 */
		public function store(array $writers): Backup
		{

			// build hash file for the current stack
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
				catch (\Exception $e) {

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
		 * @param string $writerName
		 * @return void
		 */
		private function write(string $writerName)
		{
			// build the writer class name
			$writerClass = 'Radiergummi\\Anacronism\\Modules\\Writers\\' . ucfirst(strtolower($writerName));

			// create an instance of the writer, which sets up a connection
			$writer = new $writerClass;

			// run eventual events before starting to write
			$beforeWriteEvent = new WriteEvent($this, $writer);
			$this->events->dispatch('beforeStartingWrite', $beforeWriteEvent);

			// write the previously generated archive to the location
			$writer->write($this->archiveFilename);

			// run eventual events after writing
			$afterWriteEvent = new WriteEvent($this, $writer);
			$this->events->dispatch('afterWriting', $afterWriteEvent);
		}

		/**
		 * export function.
		 * exports the backup using the set exporter
		 *
		 * @access private
		 * @return Backup instance for chaining
		 */
		private function export(): Backup
		{
			// build the exporter class name
			$exporterClass = 'Radiergummi\\Anacronism\\Modules\\Exporters\\' . ucfirst(strtolower($this->exporter));

			// create an instance of the exporter
			$archive = new $exporterClass($this->basePath, $this->archiveFilename);

			$beforeExportEvent = new ExportEvent($this, $archive);
			$this->events->dispatch('beforeStartingExport', $beforeExportEvent);

			// add the current file list
			$archive->add($this->fileList);

			// run eventual hooks before finalizing the archive
			$afterExportEvent = new ExportEvent($this, $archive);
			$this->events->dispatch('beforeClosingArchive', $afterExportEvent);

			// finalize the archive
			$archive->close();

			// return this for chaining
			return $this;
		}

		/**
		 * Magic method to directly start generators on the instance
		 *
		 * @access public
		 * @param string $method    the generator
		 * @param array  $arguments the arguments given with the generator
		 * @return Backup           the call status
		 */
		public function __call(string $method, array $arguments): Backup
		{
			if (in_array($method, get_class_methods($this))) {
				return null;
			}

			// format the generator name
			$generatorName = 'Radiergummi\\Anacronism\\Modules\\Generators\\' . ucfirst(strtolower($method));

			// create a new generator instance
			$generator = new $generatorName($this->basePath, $arguments);

			try {
				// retrieve a list of files to include in the archive
				$files = call_user_func([ $generator, 'getFileList' ]);

				// add each file to the file list
				$this->fileList = array_merge($this->fileList, $files);
			} // catch an eventual exception
			catch (\Exception $e) {

				// append this incidence to the logfile
				Log::append($e->getMessage());
			}

			// return object for chaining
			return $this;
		}

		/**
		 * getFileList function.
		 * returns the file list
		 *
		 * @access public
		 * @return array
		 */
		public function getFileList(): array
		{
			return $this->fileList;
		}

		/**
		 * inspect function.
		 * debug method
		 *
		 * @access public
		 * @return array
		 */
		public function inspect()
		{
			$reflector  = new \ReflectionObject($this);
			$properties = $reflector->getProperties();
			$methods    = $reflector->getMethods();
			$name       = $reflector->getName();

			return [
				$name => [
					'properties' => $properties,
					'methods'    => $methods
				]
			];
		}
	}
