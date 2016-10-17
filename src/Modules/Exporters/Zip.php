<?php
	namespace Radiergummi\Anacronism\Modules\Exporters;

	use Radiergummi\Anacronism\Exporter;
	use ZipArchive;

	/**
	 * Zip class.
	 * @implements \Radiergummi\Anacronism\Exporter
	 */
	class Zip implements Exporter
	{
		/**
		 * archive
		 * the archive handle
		 *
		 * @var mixed
		 * @access private
		 */
		private $archive;

		/**
		 * basePath
		 * the path to the backup folder
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
		 * @param string $path     the path to store the archive in
		 * @param string $filename the archives filename
		 */
		public function __construct(string $path, string $filename)
		{
			// create a trailing slash and cut any existing
			$this->basePath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

			// create a new zip instance
			$this->archive = new ZipArchive();

			// open the new archive
			$this->archive->open(
				$this->basePath . DIRECTORY_SEPARATOR . $filename,
				ZipArchive::CREATE | ZipArchive::OVERWRITE
			);
		}

		/**
		 * add function.
		 *
		 * @access public
		 * @param array $fileList
		 * @return Exporter
		 */
		public function add(array $fileList): Exporter
		{
			foreach ($fileList as $file) {

				// Get real and relative path for current file
				$filePath     = $file->getRealPath();
				$relativePath = substr($filePath, strlen($this->basePath));

				// Add current file to archive
				$this->archive->addFile($filePath, $relativePath);
			}

			return $this;
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
