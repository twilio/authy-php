# Resty.php

A simple PHP library for doing RESTful HTTP stuff. Does *not* require the curl extension.

## Example

```php
<?php
require __DIR__."/Resty.php";

$resty = new Resty();
$resty->debug(true);
$resty->setBaseURL('https://gimmebar.com/api/v1/');
$resp = $resty->get('public/assets/funkatron');
print_r($resp);
```