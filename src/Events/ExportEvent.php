<?php
	namespace Radiergummi\Anacronism\Events;

	use Radiergummi\Anacronism\Backup;
	use Symfony\Component\EventDispatcher\Event;

	class ExportEvent extends Event
	{
		protected $backup;

		protected $archive;

		public function __construct(Backup $backup, $archive)
		{
			$this->backup = $backup;
			$this->archive = $archive;
		}
	}
