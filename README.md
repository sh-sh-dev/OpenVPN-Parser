# OpenVPN-Parser

Parse OpenVPN status files using php

# Usage

* Install using composer:
```shell script
composer require sh_sh_dev/openvpn-parser
``` 

After including composer dependencies in your project, just use the class:

```php
Use Shay3gan\OpenVPN\Parser;

$openvpn = new Parser();
$openvpn->setStatusFile("openvpn-status.log");

print_r($openvpn->parse());
``` 

