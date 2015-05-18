# dface/criteria

Library to parse/build filter expressions. Extracted from my private project to use in other projects.


## Setup

Add to your composer.json file:

``` json
 
{
   "require": {
      "dface/criteria": "dev-master"
  }
}
```

Library organized according to PSR-0. 

So you can use composer autoloader:
``` php
require 'vendor/autoload.php';
```
or use custom PSR-0 loader.


## Tests

```
phpunit --bootstrap tests/bootstrap.php tests/
```
