<?php

require_once __DIR__.'/Authy/TestCase.php';

spl_autoload_register(function($class)
{
    $file = __DIR__.'/../lib/Authy/'.str_replace('Authy', '', $class).'.php';

    if (file_exists($file)) {
        require $file;
        return true;
    }
});

