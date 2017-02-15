<?php
use Publishpress\Autoloader;

// Here you can initialize variables that will be available to your tests
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress');
}

define('PUBLISHPRESS_ROOT_PATH', realpath(__DIR__ . '/../../src'));

// Bootstrap the include file
require_once PUBLISHPRESS_ROOT_PATH . '/autoloader.php';

// Load vendor libraries
require_once PUBLISHPRESS_ROOT_PATH . '/vendor/autoload.php';

// Register PublishPress' libraries
Autoloader::register('Publishpress\\', PUBLISHPRESS_ROOT_PATH . '/library');

define('PUBLISHPRESS_LOADED', 1);
