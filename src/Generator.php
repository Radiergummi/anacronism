<?php
	namespace Radiergummi\Anacronism;

	/**
	 * Generator interface.
	 */
	interface Generator
	{
		/**
		 * getFileList function.
		 * returns a list of files to include in the backup
		 *
		 * @access public
		 * @return array
		 */
		public function getFileList(): array;
	}
