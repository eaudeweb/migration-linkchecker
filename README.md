## Migration Link-Checker 
Checks the status of pages in a staging environment against a predefined list of links.

### Prerequisites
A .yml file - see _example.config.yml_ template 

### Installation
Run `composer install`

### Usage
* `php saicmTestUrl.php` tests HTTP status codes from _example.config.yml_ or _config.yml_ if this file exists
* `php saicmTestUrl.php -c path_to_config_file --config` tests HTTP status codes from the requested config file