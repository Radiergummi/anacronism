<?php
	namespace Radiergummi\Anacronism\Modules\Generators;

	use Radiergummi\Anacronism\Generator;
	use Radiergummi\Anacronism\DatabaseGenerator;

	/**
	 * MysqlDump class.
	 *
	 * @extends    DatabaseGenerator
	 * @implements Generator
	 */
	class MysqlDump extends DatabaseGenerator implements Generator
	{
		/**
		 * dumpDatabase function.
		 * Creates a database dump using a shell command
		 *
		 * @example $dump = new MysqlDump('foo');
		 * @access  private
		 * @param string $database (default value: --all-databases)  the database to dump,
		 *                         defaulting to all databases
		 */

		/**
		 * MysqlDump constructor.
		 *
		 * @param string $username
		 * @param string $password
		 * @param string $hostname
		 * @param string $database
		 */
		public function __construct(string $username, string $password, string $hostname = 'localhost', string $database = '')
		{
			// return false if we have no shell access
			if (! $this->checkShellAccess()) {
				$this->connect($this->buildDsn('mysql', $username, $password, $hostname, $database));
				// TODO: Work with the PDO
			}

			if (! $database) {
				$database = '--all-databases';
			}

			// execute a mysql dump via commandline
			shell_exec('mysqldump -u' . $username . ' -p' . $password . ' -h ' . $hostname . ' ' . $database);
		}

		public function getFileList(): array
		{
			// TODO: Implement getFileList() method.
		}

		/**
		 * checkShellAccess function.
		 * checks whether shell access is disabled or not
		 *
		 * @access public
		 * @return bool  whether shell access is available
		 */
		private function checkShellAccess(): bool
		{
			$disabled = explode(',', ini_get('disable_functions'));

			// if exec is within the disabled functions section of the php.ini, return false
			return ! in_array('exec', $disabled);
		}
	}
