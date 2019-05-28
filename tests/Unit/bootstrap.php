<?php
/**
 * Bootstraps the WP Rocket Plugin Unit Tests
 *
 * @package WP_Rocket\Tests\Unit
 */

if (version_compare(phpversion(), '5.6.0', '<')) {
    die('WP Rocket Plugin Unit Tests require PHP 5.6 or higher.');
}

define('WP_ROCKET_PLUGIN_TESTS_ROOT', __DIR__);
define('WP_ROCKET_PLUGIN_ROOT', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR);

$rocket_common_autoload_path = WP_ROCKET_PLUGIN_ROOT . 'vendor/';

if (! file_exists($rocket_common_autoload_path . 'autoload.php')) {
    die('Whoops, we need Composer before we start running tests.  Please type: `composer install`.  When done, try running `phpunit` again.');
}

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', WP_ROCKET_PLUGIN_ROOT );
}

require_once $rocket_common_autoload_path . 'autoload.php';

unset($rocket_common_autoload_path);
