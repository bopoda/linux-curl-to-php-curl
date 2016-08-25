<?php

class Autoloader
{
	public static function autoload($className)
	{
		$classPath = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';

		if (file_exists($classPath)) {
			require_once $classPath;
		}
		else {
			return false;
		}
	}
}

spl_autoload_register(array('Autoloader', 'autoload'));