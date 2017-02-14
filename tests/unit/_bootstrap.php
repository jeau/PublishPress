<?php
// Here you can initialize variables that will be available to your tests
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress');
}

if (!defined('PUBLISHPRESS_ROOT_PATH')) {
    define('PUBLISHPRESS_ROOT_PATH', realpath(__DIR__ . '/../../src'));
}

require_once PUBLISHPRESS_ROOT_PATH . '/include.php';
