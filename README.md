## Migration Link-Checker 
Checks the status of pages in a staging environment against a predefined list of links.


### Prerequisites
A config.yml file - see _example.config.yml_ template 

### Installation
Run `composer install`

### Usage
* `php main.php` tests HTTP status codes from _example.config.yml_ or _config.yml_ if this file exists
* `php main.php -cpath_to_config_file` or `php main.php --config path_to_config_file` 
* tests HTTP status codes from the requested config file

### Logging configuration
`example.log4php.xml` file is the default configuration for the logger. It currently logs 
all messages to _statusReport.log_ file and only errors to console.
\
To edit logging configuration, create a new file `log4php.xml`.
