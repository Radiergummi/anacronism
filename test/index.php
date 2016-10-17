<?php
	echo '<style>body{font-family:sans-serif}pre{background:rgba(0,0,0,.05);border-radius:4px;padding:5px;}code{border-bottom:3px solid rgba(0,0,0,.05)}</style>';
	use Radiergummi\Anacronism\Backup;

	# require autoloader
	require '../vendor/autoload.php';

	# instantiate the backup
	$backup = new Backup(sprintf('backup-%s', time()), 'zip');
	$backup->folder('../vendor');
	$backup->store(['dummy']);

	$file = new SPLFileInfo(realpath('../src/Backup.php'));
	$inspected = $backup->inspect();

	echo '<ul>';
	foreach ($inspected as $class => $data) {
		echo '<li><h2><code>' . $class . '</h2></code>';
		echo '<ul>';
		foreach ($data as $key => $values) {
			echo '<li><h3><code>' . $key . '</h3></code>';
			echo '<ul id="' . $key . '">';
			foreach($values as $value) {
				if ($value instanceof \ReflectionProperty) {
					$value->setAccessible(true);
					echo '<li><h4><code>' . $value->getName() . '</code>' . ($value->isPublic() ? ' [public]' : '') . '</h4><pre>' . json_encode($value->getValue($backup), JSON_PRETTY_PRINT) . '</pre>';
				} else {
					echo '<li><h4><code>' . $value->getName() . '</code>' . ($value->isPublic() ? ' [public]' : '') . '</h4><pre>' . $value . '</pre></li>';
				}
			}
			echo '</ul>';
		}
		echo '</ul>';
		echo '</li>';
	}

	echo '</ul>';
	echo '<h2>Hash von backup.php: <code>';
	echo murmurhash3($file->getRealPath() . $file->getSize() . $file->getCTime());
	echo '</code></h2>';

