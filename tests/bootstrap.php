<?php

require_once __DIR__.'/Authy/TestCase.php';

// Based on https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
spl_autoload_register(function($className)
{
    $className = ltrim($className, '\\');
    $baseDir  = __DIR__.'/../lib/';
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    if (file_exists($baseDir.'/'.$fileName)) {
        require $baseDir.'/'.$fileName;
        return true;
    } else if (file_exists($baseDir.'/vendor/'.$fileName)) {
        require $baseDir.'/vendor/'.$fileName;
        return true;
    } else {
        print("File not found for ". $className .": ".$fileName);
    }
});

