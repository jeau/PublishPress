<?php
// This is global bootstrap for autoloading

$phpVersion = getenv('PHP_VERSION');
$wpVersion = getenv('WP_VERSION');

echo "=============================================";
echo "\nPHP version: " . $phpVersion;
echo "\nWordPress version: " . $wpVersion;
echo "\nMySQL version: " . exec('mysql --version|awk \'{ print $5 }\'|awk -F\, \'{ print $1 }\'');
echo "\nPlugin slug: " . getenv('PLUGIN_SLUG');
echo "\n=============================================";
echo "\n";
echo "\n";
