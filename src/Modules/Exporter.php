<?php
	namespace Radiergummi\Anacronism\Modules;

	/**
	 * Exporter interface.
	 */
	interface Exporter
	{
		/**
		 * add function.
		 * accepts a list of files to include in the archive
		 *
		 * @access public
		 * @param array $fileList a list of files to include
		 * @return Exporter
		 */
		public function add(array $fileList): Exporter;

		/**
		 * close function.
		 * closes the current handle and writes it to disk.
		 *
		 * @access public
		 * @return void
		 */
		public function close();
	}
