<?php
/**
 * Plugin Name:       WAP Client
 * Plugin URI:        https://github.com/group-one/wap-client
 * Description:       WordPress AI Platform (WAP) client library. Integrates the WAP AI chat assistant into any WordPress plugin via a single static method call.
 * Version:           3.1.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Group.one
 * Author URI:        https://www.group.one
 * License:           GPL-2.0-or-later
 * License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html
 * Text Domain:       wap-client
 */

declare(strict_types=1);

use WP_Rocket\Dependencies\GroupOne\WapClient\AppPasswordGuard;
use WP_Rocket\Dependencies\GroupOne\WapClient\AppPasswordLock;
use WP_Rocket\Dependencies\GroupOne\WapClient\AppPasswordManager;
use WP_Rocket\Dependencies\GroupOne\WapClient\AppPasswordScopeEnforcer;
use WP_Rocket\Dependencies\GroupOne\WapClient\Bootstrap;
use WP_Rocket\Dependencies\GroupOne\WapClient\CallableGrndProvider;
use WP_Rocket\Dependencies\GroupOne\WapClient\ChatColumn;
use WP_Rocket\Dependencies\GroupOne\WapClient\ChatEmbed;
use WP_Rocket\Dependencies\GroupOne\WapClient\ChatWidget;
use WP_Rocket\Dependencies\GroupOne\WapClient\Config;
use WP_Rocket\Dependencies\GroupOne\WapClient\ConsentGate;
use WP_Rocket\Dependencies\GroupOne\WapClient\GdprHandler;
use WP_Rocket\Dependencies\GroupOne\WapClient\GrndProviderInterface;
use WP_Rocket\Dependencies\GroupOne\WapClient\GrndService;
use WP_Rocket\Dependencies\GroupOne\WapClient\LicenseGrndProvider;
use WP_Rocket\Dependencies\GroupOne\WapClient\ScreenTarget;
use WP_Rocket\Dependencies\GroupOne\WapClient\TokenManager;
use WP_Rocket\Dependencies\GroupOne\WapClient\TokenSealer;
use WP_Rocket\Dependencies\GroupOne\WapClient\UserRef;
use WP_Rocket\Dependencies\GroupOne\WapClient\WapClient;
use WP_Rocket\Dependencies\GroupOne\WapClient\WrapKeyClient;

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
    // ::class (not strings) so a namespace-prefixing tool such as Mozart rewrites these.
    $class_map = [
        AppPasswordManager::class       => __DIR__ . '/includes/class-app-password-manager.php',
        AppPasswordGuard::class         => __DIR__ . '/includes/class-app-password-guard.php',
        AppPasswordLock::class          => __DIR__ . '/includes/class-app-password-lock.php',
        AppPasswordScopeEnforcer::class => __DIR__ . '/includes/class-app-password-scope-enforcer.php',
        ChatColumn::class               => __DIR__ . '/includes/class-chat-column.php',
        ChatEmbed::class                => __DIR__ . '/includes/class-chat-embed.php',
        ChatWidget::class               => __DIR__ . '/includes/class-chat-widget.php',
        ScreenTarget::class             => __DIR__ . '/includes/class-screen-target.php',
        Config::class                   => __DIR__ . '/includes/class-config.php',
        ConsentGate::class              => __DIR__ . '/includes/class-consent-gate.php',
        GdprHandler::class              => __DIR__ . '/includes/class-gdpr-handler.php',
        GrndService::class              => __DIR__ . '/includes/class-grnd-service.php',
        GrndProviderInterface::class    => __DIR__ . '/includes/class-grnd-provider.php',
        LicenseGrndProvider::class      => __DIR__ . '/includes/class-grnd-provider.php',
        CallableGrndProvider::class     => __DIR__ . '/includes/class-grnd-provider.php',
        TokenManager::class             => __DIR__ . '/includes/class-token-manager.php',
        TokenSealer::class              => __DIR__ . '/includes/class-token-sealer.php',
        WrapKeyClient::class            => __DIR__ . '/includes/class-wrap-key-client.php',
        Bootstrap::class                => __DIR__ . '/includes/class-bootstrap.php',
        UserRef::class                  => __DIR__ . '/includes/class-user-ref.php',
        WapClient::class                => __DIR__ . '/includes/class-wap-client.php',
    ];

    spl_autoload_register(static function (string $class) use ($class_map): void {
        if (isset($class_map[$class])) {
            require_once $class_map[$class];
        }
    });
}

// ---------------------------------------------------------------------------
// Constants — kept for consumers that read them; the library itself reads WP_Rocket\Dependencies\GroupOne\WapClient\Config.
// ---------------------------------------------------------------------------

defined('WAP_CLIENT_VERSION') || define('WAP_CLIENT_VERSION', Config::VERSION);
defined('WAP_CLIENT_FILE') || define('WAP_CLIENT_FILE', __FILE__);
defined('WAP_CLIENT_DIR') || define('WAP_CLIENT_DIR', plugin_dir_path(__FILE__));
defined('WAP_CLIENT_URL') || define('WAP_CLIENT_URL', plugin_dir_url(__FILE__));

// ---------------------------------------------------------------------------
// Activation / deactivation hooks
// ---------------------------------------------------------------------------

register_activation_hook(__FILE__, 'wap_client_activate');
register_deactivation_hook(__FILE__, 'wap_client_deactivate');

// Declarations live in their own file, required only past the guard: PHP declares a file's top-level
// classes and functions at compile time, so here they would exist even without WordPress.
//
// Skipped when another copy of the library already declared them (two plugins each bundling the library under
// their own namespace prefix, e.g. with Mozart): PHP can't redeclare a function or class, so the first copy wins.
if (!function_exists('wap_client_boot') && !class_exists('WapClient', false)) {
    require_once __DIR__ . '/wap-client-api.php';
}
