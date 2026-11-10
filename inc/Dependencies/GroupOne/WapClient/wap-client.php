<?php
/**
 * Plugin Name:       WAP Client
 * Plugin URI:        https://github.com/group-one/wap-client
 * Description:       WordPress AI Platform (WAP) client library. Integrates the WAP AI chat assistant into any WordPress plugin via a single static method call.
 * Version:           2.6.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Group.one
 * Author URI:        https://www.group.one
 * License:           GPL-2.0-or-later
 * License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html
 * Text Domain:       wap-client
 */

declare(strict_types=1);

// return, not exit: Composer autoloads this file, and exit would end PHPUnit/PHPCS/PHPStan runs in consumers.
if (!defined('ABSPATH')) {
    return;
}

// ---------------------------------------------------------------------------
// Autoloader — PSR-4 via Composer, or manual fallback for non-Composer installs.
// ---------------------------------------------------------------------------

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Manual class map fallback when Composer is not available.
    $class_map = [
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\AppPasswordManager'       => __DIR__ . '/includes/class-app-password-manager.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\AppPasswordGuard'         => __DIR__ . '/includes/class-app-password-guard.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\AppPasswordLock'          => __DIR__ . '/includes/class-app-password-lock.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\AppPasswordScopeEnforcer' => __DIR__ . '/includes/class-app-password-scope-enforcer.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\ChatColumn'               => __DIR__ . '/includes/class-chat-column.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\ChatEmbed'                => __DIR__ . '/includes/class-chat-embed.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\ChatWidget'               => __DIR__ . '/includes/class-chat-widget.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\ScreenTarget'             => __DIR__ . '/includes/class-screen-target.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\ConsentGate'              => __DIR__ . '/includes/class-consent-gate.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\GdprHandler'              => __DIR__ . '/includes/class-gdpr-handler.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\GrndService'              => __DIR__ . '/includes/class-grnd-service.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\GrndProviderInterface'    => __DIR__ . '/includes/class-grnd-provider.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\LicenseGrndProvider'      => __DIR__ . '/includes/class-grnd-provider.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\CallableGrndProvider'     => __DIR__ . '/includes/class-grnd-provider.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\TokenManager'             => __DIR__ . '/includes/class-token-manager.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\TokenSealer'              => __DIR__ . '/includes/class-token-sealer.php',
        'WP_Rocket\\Dependencies\\GroupOne\\WapClient\\WrapKeyClient'            => __DIR__ . '/includes/class-wrap-key-client.php',
    ];

    spl_autoload_register(static function (string $class) use ($class_map): void {
        if (isset($class_map[$class])) {
            require_once $class_map[$class];
        }
    });
}

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

define('WP_ROCKET_WAP_CLIENT_VERSION', '2.6.0');
define('WP_ROCKET_WAP_CLIENT_FILE', __FILE__);
define('WP_ROCKET_WAP_CLIENT_DIR', plugin_dir_path(__FILE__));
define('WP_ROCKET_WAP_CLIENT_URL', plugin_dir_url(__FILE__));


// Declarations live in their own file, required only past the guard: PHP declares a file's top-level
// classes and functions at compile time, so here they would exist even without WordPress.
require_once __DIR__ . '/wap-client-api.php';
