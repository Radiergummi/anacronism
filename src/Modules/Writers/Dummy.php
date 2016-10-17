<?php
	namespace Radiergummi\Anacronism\Modules\Writers;

	use Radiergummi\Anacronism\Helpers\Log;
	use Radiergummi\Anacronism\Modules\Writer;

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
		 * @param array $files
		 * @return void
		 */
		public function write(array $files)
		{
			echo '<pre>';
			echo var_export($files);
			echo '</pre>';
			Log::append('Dummy output to browser!', 1);
		}
	}
