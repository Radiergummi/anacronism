<?php
	namespace Radiergummi\Anacronism\Modules\Writers;

	use Radiergummi\Anacronism\Helpers\Log;
	use Radiergummi\Anacronism\Writer;

	/**
	 * Dummy class.
	 */
	class Dummy implements Writer
	{
		/**
		 * write function.
		 * writes an archive.
		 *
		 * @access public
		 * @param string $archivePath
		 * @return void
		 */
		public function write(string $archivePath)
		{
			echo '<pre>';
			echo var_export($archivePath);
			echo '</pre>';
			Log::append('Dummy output to browser!', 1);
		}
	}
