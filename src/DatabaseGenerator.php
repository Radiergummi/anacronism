<?php
	namespace Radiergummi\Anacronism;

	/**
	 * Database class.
	 * Base class for database generators
	 */
	abstract class DatabaseGenerator
	{
		/**
		 * the database PDO handle
		 *
		 * @var \PDO
		 * @access protected
		 */
		protected $instance;

		/**
		 * Database connector.
		 *
		 * @access public
		 * @param string $dsn
		 * @return \Radiergummi\Anacronism\DatabaseGenerator
		 */
		public function connect(string $dsn): DatabaseGenerator
		{
			$this->instance = new \PDO($dsn);

			return $this;
		}

		/**
		 * buildDsn function.
		 * builds the database source name for the PDO constructor
		 *
		 * @access protected
		 * @param string $protocol
		 * @param string $username
		 * @param string $password
		 * @param string $hostname
		 * @param string $database
		 *
		 * @return string
		 */
		protected function buildDsn(string $protocol, string $username, string $password, string $hostname, string $database): string
		{
			return sprintf('%s://%s:%s@%s/%s', $protocol, $username, $password, $hostname, $database);
		}
	}
