<?php

/**
 * Autoloader
 *
 * PHP version 5
 *
 * @category Services
 * @package  Authy
 * @author   David Cuadrado <david@authy.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 * @link     http://authy.github.com/pear
 */

/**
 * Autoloads Authy API files
 * Based on https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
 *
 * @param string $className class to load
 *
 * @return boolean true when the file was loaded
 */
function Authy_autoloader($className)
{
    $className = ltrim($className, '\\');
    $baseDir  = __DIR__.'/../lib/';
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', '/', $namespace) . '/';
    }
    $fileName .= str_replace('_', '/', $className) . '.php';

    if (file_exists($baseDir.'/'.$fileName)) {
        include $baseDir.'/'.$fileName;
        return true;
    } else if (file_exists($baseDir.'/vendor/'.$fileName)) {
        include $baseDir.'/vendor/'.$fileName;
        return true;
    } else {
        print("File not found for ". $className .": ".$fileName);
    }

    return false;
}

spl_autoload_register('Authy_autoloader');
