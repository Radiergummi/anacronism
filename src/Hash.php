<?php
	namespace Radiergummi\Anacronism;

	/**
	 * Class Hash
	 *
	 * @package Radiergummi\Anacronism
	 */
	class Hash
	{
		/**
		 * the files to hash
		 *
		 * @var array
		 */
		protected $fileList;

		/**
		 * the hashed representation
		 *
		 * @var array
		 */
		protected $hashSet;

		/**
		 * Hash constructor.
		 * creates a new hash set from a list of files
		 *
		 * @constructor
		 * @access public
		 * @param array $fileList
		 */
		public function __construct(array $fileList)
		{
			$this->fileList = $fileList;

			foreach ($this->fileList as $filePath) {
				$file = new \SplFileInfo($filePath);

				// hash the file path, size and CTime using murmur
				$this->hashSet[ $file->getRealPath() ] = murmurhash3(
					$file->getRealPath() .
					$file->getSize() .
					$file->getCTime()
				);
			}
		}

		/**
		 * compare function.
		 * compares two hash sets
		 *
		 * @access public
		 * @param \Radiergummi\Anacronism\Hash $hashSet
		 *
		 * @return bool
		 */
		public function compare(Hash $hashSet)
		{
			return ("$this" === "$hashSet");
		}

		/**
		 * read function.
		 * reads the hash table from a dump file
		 *
		 * @access public
		 * @param string $filePath
		 * @return void
		 */
		public function read(string $filePath)
		{
			$this->hashSet = json_decode(file_get_contents($filePath));
		}

		/**
		 * dump function.
		 * dumps the hash table as JSON into a file
		 *
		 * @access public
		 * @param string $filePath
		 * @return void
		 */
		public function dump(string $filePath)
		{
			file_put_contents($filePath, json_encode($this->hashSet));
		}

		/**
		 * __toString function.
		 * returns the hash set as a hashed string representation.
		 *
		 * @access public
		 * @return string
		 */
		public function __toString()
		{
			return murmurhash3(json_encode($this->hashSet));
		}

		/**
		 * getHashSet function.
		 * hashSet getter
		 *
		 * @access public
		 * @return array
		 */
		public function getHashSet()
		{
			return $this->hashSet;
		}
	}
