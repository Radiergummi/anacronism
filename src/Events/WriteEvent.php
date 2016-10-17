<?php
	namespace Radiergummi\Anacronism\Events;

	use Symfony\Component\EventDispatcher\Event;

	class WriteEvent extends Event
	{
		protected $backup;

		protected $writer;

		public function __construct($backup, $writer)
		{
			$this->backup = $backup;
			$this->writer = $writer;
		}
	}
