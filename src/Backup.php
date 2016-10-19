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
		 * the hashed backup representation
		 *
		 * @var \Radiergummi\Anacronism\Hash
		 */
		private $hash;

		/**
		 * the filename for the archive generated
		 * (default value: '')
		 *
		 * @var string
		 */
		private $archiveFilename;

		/**
		 * the selected compressor (zip, tar, bzip..)
		 * (default value: null)
		 *
		 * @access private
		 * @var string
		 */
		private $compressor;

		/**
		 * basePath
		 * the path to store all backup related files in
		 * (default value: '')
		 *
		 * @var string
		 * @access private
		 */
		private $basePath;

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
		 * @param array $options
		 */
		public function __construct(array $options)
		{
			// set the archive name to the parameter value
			$this->archiveFilename = 'placeholder';

			// set base path to this files folder
			$this->setBasePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'temp');

			// load the events library
			$this->events = new EventDispatcher();

			$this->events->dispatch('backupStarted');
		}

		/**
		 * add function.
		 * adds multiple generators to the backup
		 *
		 * @access public
		 * @param string|array $generators
		 * @param array        $arguments (default value: [])
		 * @return Backup
		 */
		public function add($generators, array $arguments = [ ]): Backup
		{
			// if we received a single string, transform it into an array
			if (is_string($generators)) {
				$generators = [
					$generators => $arguments
				];
			}

			foreach ($generators as $generatorName => $arguments) {
				$this->generate($generatorName, $arguments);
			}

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
		public function __call(string $method, array $arguments = [ ]): Backup
		{
			return $this->generate(substr($method, 3), $arguments);
		}

		/**
		 * compressWith function.
		 * adds a compressor to the backup
		 *
		 * @access
		 * @param $compressor
		 *
		 * @return \Radiergummi\Anacronism\Backup
		 */
		public function compressWith($compressor)
		{
			$this->compressor = $compressor;
			$this->compress();

			/**
			 * Anonymous class to enable compressor chaining.
			 * That means you can compress your archive with tar,
			 * then with gzip for example.
			 * This class provides a "andWith()" method. All other
			 * calls will be applied to the original backup,
			 * so once you call "saveAt" for example, you will
			 * receive the backup object again.
			 */
			return new class($this) {
				protected $backup;

				/**
				 * creates a new instance and sets the backup
				 *
				 * @param Backup $backup
				 */
				public function __construct(Backup $backup)
				{
					$this->backup = $backup;
				}

				/**
				 * andWith function.
				 * adds another compressor
				 *
				 * @access public
				 * @param string $compressor
				 *
				 * @return $this
				 */
				public function andWith(string $compressor)
				{
					$this->backup->compressWith($compressor);
					return $this;
				}

				public function __call(string $method, array $arguments): Backup
				{
					return $this->backup->$method(...$arguments);
				}
			};
		}

		/**
		 * store function.
		 * closes and stores the backup to all specified locations
		 *
		 * @access public
		 * @param array $writers the writers to write the backup to
		 * @return Backup        for chaining
		 */
		public function saveAt(array $writers): Backup
		{
			// build hash file for the current stack. This will be more useful later on, once
			// diff backups are possible - here, we could already determine if there have been any
			// changed files at all and if not, simply reuse the previous backup to save disk space.
			$this->buildHashFile();

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

			// return the chaining object to attach more writers
			return new class($this) {
				protected $backup;

				/**
				 * creates a new object and sets the backup
				 *
				 * @param Backup $backup
				 */
				public function __construct(Backup $backup)
				{
					$this->backup = $backup;
				}

				/**
				 * andAt function.
				 * allows to add another location.
				 *
				 * @access public
				 * @param string $writer
				 * @return $this
				 */
				public function andAt(string $writer)
				{
					$this->backup->saveAt([$writer]);

					return $this;
				}

				/**
				 * __call function.
				 * forward all other calls to the original backup class
				 *
				 * @access public
				 * @param string $method
				 * @param array $arguments
				 *
				 * @return Backup
				 */
				public function __call(string $method, array $arguments): Backup
				{
					return $this->backup->$method(...$arguments);
				}
			};
		}

		/**
		 * __toString function.
		 * returns the current backup file path
		 *
		 * @access
		 * @return string
		 */
		public function __toString()
		{
			return $this->basePath . $this->archiveFilename;
		}

		/**
		 * runGenerator function.
		 * instantiates a generator and processes its file list
		 *
		 * @access
		 * @param       $generatorName
		 * @param array $arguments
		 * @return Backup
		 */
		private function generate(string $generatorName, $arguments = [ ]): Backup
		{

			// format the generator name
			$generatorClass = 'Radiergummi\\Anacronism\\Modules\\Generators\\' . ucfirst(strtolower($generatorName));

			// create a new generator instance, passing all arguments as parameters
			$generator = new $generatorClass($this->basePath, ...$arguments);

			try {
				// retrieve a list of files to include in the archive
				$files = $generator->getFileList();

				// add each file to the file list
				$this->fileList = array_merge($this->fileList, $files);
			} catch (\Exception $e) {

				// append this incidence to the logfile
				Log::append($e->getMessage());
			}

			// return object for chaining
			return $this;
		}

		/**
		 * compress function.
		 * compresses the backup using the set compressor. This is optional.
		 *
		 * @access private
		 * @return Backup instance for chaining
		 */
		private function compress(): Backup
		{
			// build the compressor class name
			$compressorClass = 'Radiergummi\\Anacronism\\Modules\\Exporters\\' . ucfirst(strtolower($this->compressor));

			// opens a new archive handle
			$archive = new $compressorClass($this->basePath, $this->archiveFilename);

			$beforeExportEvent = new ExportEvent($this, $archive);
			$this->events->dispatch('beforeStartingExport', $beforeExportEvent);

			// add the current file list to the archive
			$archive->add($this->fileList);

			// run eventual hooks before finalizing the archive
			$afterExportEvent = new ExportEvent($this, $archive);
			$this->events->dispatch('beforeClosingArchive', $afterExportEvent);

			// finalize the archive
			$archive->close();

			$this->fileList = [
				new \SplFileInfo($this->basePath . $this->archiveFilename)
			];

			// return this for chaining
			return $this;
		}

		/**
		 * write function.
		 *
		 * @access private
		 * @param string $writerName
		 * @return Backup
		 */
		private function write(string $writerName): Backup
		{
			// build the writer class name
			$writerClass = 'Radiergummi\\Anacronism\\Modules\\Writers\\' . ucfirst(strtolower($writerName));

			// create an instance of the writer, which sets up a connection
			$writer = new $writerClass;

			// run eventual events before starting to write
			$beforeWriteEvent = new WriteEvent($this, $writer);
			$this->events->dispatch('beforeStartingWrite', $beforeWriteEvent);

			// write the previously generated archive to the location
			$writer->write($this->fileList);

			// run eventual events after writing
			$afterWriteEvent = new WriteEvent($this, $writer);
			$this->events->dispatch('afterWriting', $afterWriteEvent);

			return $this;
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
		private function setBasePath(string $path)
		{
			if (! realpath($path)) {
				mkdir($path);
			}

			$this->basePath = realpath($path) . DIRECTORY_SEPARATOR;
		}

		/**
		 * buildHashFile function.
		 *
		 * @access private
		 * @return void
		 */
		private function buildHashFile()
		{
			$this->hash = new Hash($this->fileList);
			$this->hash->dump($this->basePath . 'lastBackup.hash.json');
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
