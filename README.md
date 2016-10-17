# anacronism
Anacronism is a modular backup engine for web projects, well suited for shared hosting.

## How it works
When coming up with Anacronism, I split the backup process into three segments: *generating data*, *exporting data* and *writing data*.  

In the first step, a collection of filesystem entries is made and additional steps, such as database dumping, are performed. In the second step, the previously collected items are compressed into an archive. In the third step, the resulting archive is written to the destination, for example a cloud storage, FTP server or another hard drive.

To make it easy to adapt Anachronism to your needs, each of those steps uses plugins, referred to as *modules*. 

## Basic usage
To create a new backup, do exactly so:

```php
$backup = new Backup('backup-' . time(), 'zip');
```
This will create an instance that will carry the name `backup-1438030738.zip` and use `Zip` as the exporter method (hence the file extension).

To actually backup some data, we add a folder to it:

```php
$backup->folder('/var/www');
```
And some mysql dump, maybe?

```php
$backup->mysqldump($pdo);
```


## Modules
### Generators
Generators (modules that generate data) can be called directly onto the instance, for example using `$backup->dumpredis()` for a class named `Dumpredis`.
So, if you wanted to include a redis dump, you'd put a file named `Dumpredis.php` in `src/Modules/Generators`
