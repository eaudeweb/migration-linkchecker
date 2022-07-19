<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

require 'vendor/autoload.php';
$logger = Logger::getLogger("default");
$config_logger_file = "example.log4php.xml";
if (file_exists(dirname(__FILE__) . "/log4php.xml")) {
    $config_logger_file = "log4php.xml";
}
Logger::configure($config_logger_file);
$file_contents = [];


/**
 * Verifies if there are the right command line args
 * and loads config file contents
 * @return bool
 */
function verifyArguments() {
    global $file_contents, $logger;
    $options = getopt("c::", ["config:"]);

    if (!count($options)) {
        $logger->debug("No config file given. Searching for config.yml in current directory");

        //search for config.yml
        $files = scandir(dirname(__FILE__));
        foreach ($files as $file_name) {
            if ($file_name == "config.yml") {
                $logger->debug("config.yml file found in " . dirname(__FILE__));
                $file_contents = yaml_parse_file(dirname(__FILE__) . "/" . "config.yml");
                return TRUE;
            }
        }

        //search for example.config.yml
        $logger->debug("No config.yml file found. Searching for example.config.yml in current directory");
        foreach ($files as $file_name) {
            if ($file_name == 'example.config.yml') {
                $logger->debug("example.config.yml file found in " . dirname(__FILE__));
                $file_contents = yaml_parse_file(dirname(__FILE__) . "/" . "example.config.yml");
                return TRUE;
            }
        }

        $logger->error(
            "No config.yml or example.config.yml found in current directory. You can specify one using -c path_to_file --config"
        );
        return FALSE;

    } else if (count($options) == 1) {
        $config_file_path = array_key_exists('config', $options) ? $options['config'] : $options['c'];
        if (@file_exists($config_file_path) && !is_dir($config_file_path)) {
            $extension = explode('.', $config_file_path);
            $extension = end($extension);
            if ($extension !== 'yml') {
                $logger->error('Wrong file type, expected yml');
                return FALSE;
            }
            $file_contents = yaml_parse_file($config_file_path);
            $logger->debug("Config file " . $config_file_path . " found");
            return TRUE;
        } else {
            $logger->error($config_file_path . ' file not found');
            return FALSE;
        }
    }

    $logger->error('Wrong arguments passed, use -c path_to_file --config');
    return FALSE;
}


/**
 * Get HTTP Response Code Status of an URL
 * @param string $url
 * requested URL
 * @return int
 * response code for that request
 * @throws GuzzleException
 */
function getHttpCode($url) {
    $client = new Client();
    $response = $client->request('HEAD', $url, ['http_errors' => false]);
    return $response->getStatusCode();
}


/**
 * Verifies URL codes from config file
 * @return void
 * @throws GuzzleException
 */
function checkUrls() {
    global $file_contents, $logger;

    $exit_code = 0;
    $logger->debug('Testing urls from config file...');
    $test_url = $file_contents['urls']['test'];
    $logger->debug("Testing pages for " . $test_url);

    foreach ($file_contents['pages'] as $_ => $links) {
        foreach ($links as $page => $expected_http_code) {
            $url = $test_url . $page;
            $actual_http_code = getHttpCode($url);
            if ($actual_http_code == $expected_http_code) {
                $logger->info('OK status code for ' . $url);
            } else {
                $exit_code = 1;
                $logger->error('Wrong status code for ' . $url . ' expected '
                    . $expected_http_code . ' got ' . $actual_http_code);
            }
        }
    }
    exit($exit_code);
}


$correct_input = verifyArguments();
if ($correct_input) {
    try {
        checkUrls();
    } catch (GuzzleException $e) {
        $logger->error($e->getMessage());
        exit(1);
    }
} else {
    exit(1);
}



