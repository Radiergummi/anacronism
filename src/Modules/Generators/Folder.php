<?php
	namespace Radiergummi\Anacronism\Modules\Generators;

	use Radiergummi\Anacronism\Helpers\DirectoryFilter;
	use Radiergummi\Anacronism\Modules\Generator;
	use RecursiveDirectoryIterator;
	use RecursiveIteratorIterator;

	/**
	 * Folder class.
	 *
	 * @implements Generator
	 */
	class Folder implements Generator
	{
		/**
		 * excludes
		 * files to explicitly exclude from the backup
		 * (default value: array())
		 *
		 * @var array
		 * @access public
		 * @static
		 */
		public $excludes = [
			'backup'
		];

		/**
		 * fileList
		 * the list of files to include in the backup
		 * (default value: array())
		 *
		 * @var array
		 * @access public
		 */
		public $fileList = [ ];

		/**
		 * basepath
		 * the base path
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
		 * @param string       $basePath    the base path
		 * @param string|array $directories the directories to include in the backup
		 * @param array        $excludes    the files and folders to explicitly exclude from the backup
		 */
		public function __construct(string $basePath, $directories, array $excludes = [ ])
		{
			// add the base path if its not set yet, adding a slash to the end if not present
			if (empty( $this->basePath )) {
				$this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			}

			// repeat for each given directory
			foreach ((array) $directories as $directory) {

				// merge existing file list with new directory
				$this->fileList = array_merge($this->fileList, $this->getDirectory($this->basePath . $directory));
			}
		}

		/**
		 * getFileList function.
		 *
		 * @access public
		 * @return array the list of files to include in the backup
		 */
		public function getFileList(): array
		{
			// return this generators file list
			return $this->fileList;
		}

		/**
		 * getDirectory function.
		 * recursively iterates over a directory
		 *
		 * @access private
		 * @param string $path the path to search
		 * @return array
		 */
		private function getDirectory(string $path): array
		{
			// create a list of files
			$files = [ ];

			// create a directory iterator, skipping current and parent directory
			$di = new RecursiveDirectoryIterator(realpath($path), RecursiveDirectoryIterator::SKIP_DOTS);

			// filter the results using the exclude array
			$filtered = new DirectoryFilter($di, $this->excludes);

			// iterate over filtered results, processing folders separately
			$iterator = new RecursiveIteratorIterator($filtered, RecursiveIteratorIterator::SELF_FIRST);

			// iterate over the directory
			foreach ($iterator as $name => $file) {

				// skip file if its a folder, they are being included anyway
				if ($file->isDir()) {
					continue;
				}

				$files[] = $file;
			}

			// return the file list
			return $files;
		}
	}
