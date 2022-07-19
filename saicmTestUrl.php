<?php

require 'vendor/autoload.php';
$logger = Logger::getLogger("default");
$file_contents = [];


/**
 * Verifies if there are the right command line args
 * and loads config file contents
 * @param $args
 * @return bool
 */
function verifyArguments($args) {
    global $file_contents, $logger;

    if (count($args) === 1) {
        $logger->info("No config file given. Searching for config.yml in current directory");

        //search for config.yml
        $files = scandir(dirname(__FILE__));
        foreach ($files as $file_name) {
            if ($file_name == "config.yml") {
                $logger->info("config.yml file found in " . dirname(__FILE__));
                $file_contents = yaml_parse_file(dirname(__FILE__) . "/" . "config.yml");
                return TRUE;
            }
        }

        //search for example.config.yml
        $logger->info("No config.yml file found. Searching for example.config.yml in current directory");
        foreach ($files as $file_name) {
            if ($file_name == 'example.config.yml') {
                $logger->info("example.config.yml file found in " . dirname(__FILE__));
                $file_contents = yaml_parse_file(dirname(__FILE__) . "/" . "example.config.yml");
                return TRUE;
            }
        }

        $logger->error(
            "No config.yml or example.config.yml found in current directory. You can specify one using -c path_to_file --config"
        );
        return FALSE;

    } else if (count($args) == 4 && $args[1] == "-c"
        && $args[3] == "--config") {
        if (@file_exists($args[2]) && !is_dir($args[2])) {
            $extension = explode('.', $args[2]);
            $extension = end($extension);
            if($extension !== 'yml') {
                $logger->error('Wrong file type, expected yml');
                return FALSE;
            }
            $file_contents = yaml_parse_file($args[2]);
            $logger->info("Config file " . $args[2] . " found");
            return TRUE;
        } else {
            $logger->error($args[2] . ' file not found');
            return FALSE;
        }
    }

    $logger->error('Wrong arguments passed, use -c path_to_file --config');
    return FALSE;
}


/**
 * Get HTTP Response Code Status of an URL
 * @param $url
 * requested URL
 * @return string
 * response code for that request
 */
function getHttpCode($url) {
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
    curl_exec($handle);
    $http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);
    return $http_code;
}


/**
 * Verifies URL codes from config file
 * @return void
 */
function checkUrls() {
    global $file_contents, $logger;
    $wrong_response = FALSE;
    $logger->info('Testing urls from config file...');

    $test_urls = is_array($file_contents['urls']['test']) ?
        $file_contents['urls']['test'] : array($file_contents['urls']['test']);
    foreach ($test_urls as $test_url) {
        $logger->info("Testing pages for " . $test_url);
        foreach ($file_contents['pages'] as $item => $array) {
            foreach ($array as $page => $expected_http_code) {
                $actual_http_code = getHttpCode($test_url . $page);
                if ($actual_http_code == $expected_http_code) {
                    $logger->info('OK status code for ' . $test_url . $page);
                } else {
                    $wrong_response = TRUE;
                    $logger->error('Wrong status code for ' . $test_url . $page . ' expected '
                        . $expected_http_code . ' got ' . $actual_http_code);
                }
            }
        }
    }
    if (!$wrong_response) {
        exit(0);
    } else {
        exit(1);
    }
}


$correct_input = verifyArguments($_SERVER['argv']);
if ($correct_input) {
    checkUrls();
} else {
    exit(1);
}



