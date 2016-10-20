# anacronism
Anacronism is a modular backup engine for web projects, well suited for shared hosting.

## How it works
When coming up with Anacronism, I split the backup process into three segments: *generating data*, *compressing data* and *writing data*.  
In the first step, a collection of filesystem entries is made and additional jobs, such as database dumping, are performed. In the second step, the previously collected items are compressed into an archive. In the third step, the resulting archive is written to the destination, for example a cloud storage, FTP server or another hard drive.

To make it easy to adapt Anachronism to your needs, each of those steps uses plugins, referred to as *modules*. 

## Basic usage
To create a new backup, do exactly so:

```php
// create the backup and add data (generators)
$backup = new Backup($options)
  ->addFolder('path/to/folder')
  ->addFolder('another/path')
  ->addMySQLDump($host, $db, $dbuser, $dbpass);
  
// optionally compress the backup with multiple compressors. They will be applied as called. (compressors)
$backup->compressWith('tar')
  ->andwith('gzip');
  
// save the backup at multiple locations (writers)
$backup->saveAt(['local' => [ 'path' => '/backups/' ]])
  ->andAt(['dropbox' => [ 'user' => $dropboxUser, 'password' => $dropboxPass, 'folder' => 'backups' ]])
  ->andAt(['sftp' => [ 'host' => $backupHost, 'user' => $backupUser, 'password' => $backupPass, 'path' => $backupPath]]);
```

## Modules
For all three types of modules there are interfaces which describe the methods the module needs to implement in order to work with Anacronism.

### Generators
Generators (modules that generate data) can be called directly onto the instance, for example using `$backup->addRedisDump()` for a class named `RedisDump`.
So, if you wanted to include a redis dump, you'd put a file named `RedisDump.php` in `src/Modules/Generators`.

### Compressors
Once the `compressWith()` method is called, you cannot add more data to the backup - it is regarded as compressed and therefore closed. Compressors can be chained to achieve multiple nested compression formats, though you should be aware that this won't necessary yield smaller archives - there is an upper limit of applicable compression, regardless of the algorithms used. You can chain compressors with `->andWith($compressor)`.

### Writers
Writers are based on [PHP League's `FlySystem`](https://github.com/thephpleague/flysystem) which provides abstraction layers for most used storage options like cloud services, (S)FTP and such. To use their integrations, you'll need a wrapper class, which is an Anacronism writer. Some of these are included or will be on request.  
Writers need to be supplied with all data necessary to write the backup - mostly, that'll be credentials and a path. You can chain writers with `->andAt([ $writer => $writerConfiguration ])`. They'll be executed sequentially.
