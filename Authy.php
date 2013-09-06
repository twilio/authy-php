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
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    require 'lib/'.$fileName;
}

spl_autoload_register('Authy_autoloader');
