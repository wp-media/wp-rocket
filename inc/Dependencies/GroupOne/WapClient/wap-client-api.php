<?php
/**
 * The library's public global names: the `\WapClient` facade alias and the wap_client_* hook callbacks.
 *
 * Required by wap-client.php only once WordPress is loaded. Not under includes/, which is PSR-4:
 * a global class there would trip Composer's optimised-autoloader warnings. The logic lives in the
 * namespaced classes (Bootstrap, WapClient), which a namespace-prefixing tool such as Mozart can rewrite.
 *
 * @package WP_Rocket\Dependencies\GroupOne\WapClient
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    return;
}

use WP_Rocket\Dependencies\GroupOne\WapClient\Bootstrap;

/**
 * On activation: grant the wap_use_ai capability to administrator and editor roles.
 */
function wap_client_activate(): void {
    Bootstrap::activate();
}

/**
 * On deactivation: a no-op, capability grants are preserved.
 */
function wap_client_deactivate(): void {
    Bootstrap::deactivate();
}

add_action('init', 'wap_client_load_textdomain');
add_action('plugins_loaded', 'wap_client_boot', 20);

/**
 * Load bundled translations for the `wap-client` text domain.
 */
function wap_client_load_textdomain(): void {
    Bootstrap::load_textdomain();
}

/**
 * Boot the WAP client after all plugins have loaded.
 */
function wap_client_boot(): void {
    Bootstrap::boot();
}

// The public facade, under its long-standing global name: `\WapClient::register_chat_page()` and friends.
class_alias(WP_Rocket\Dependencies\GroupOne\WapClient\WapClient::class, 'WapClient');
