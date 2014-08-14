<?php
$filename = '/vendor/autoload.php';
$paths = [__DIR__.$filename, __DIR__.'/../../..'.$filename];
foreach($paths as $path){
    if (file_exists($path))
        require $path;
}

class Authy_Api extends Authy\AuthyApi {}

?>
