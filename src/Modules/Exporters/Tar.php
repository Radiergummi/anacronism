<?php
	namespace Radiergummi\Anacronism\Modules\Exporters;

	use Radiergummi\Anacronism\Modules\Exporter;

	/**
	 * Tar class.
	 * @implements \Radiergummi\Anacronism\Modules\Exporter
	 */
	class Tar implements Exporter
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
			// TODO: Implement TAR
		}

		/**
		 * add function.
		 *
		 * @access public
		 * @param array $files
		 * @return Tar
		 */
		public function add(array $files): Tar
		{
			foreach ($files as $file) {

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
