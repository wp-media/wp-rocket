<?php
namespace WP_Rocket\Subscriber\Third_Party\Themes;

/**
 * Compatibility file for Elementor plugin
 *
 * @since 3.3.1
 * @author Remy Perona
 */
class Elementor_Subscriber implements Subscriber_Interface {
    /**
     * @inheritDoc
     */
    public static function get_subscribed_events() {
        if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
            return [];
        }

        return [
            'wp_rocket_loaded' => 'remove_widget_callback',
        ];
    }

    /**
     * Remove the callback to clear the cache on widget update
     *
     * @since 3.3.1
     * @author Remy Perona
     *
     * @return void
     */
    public function remove_widget_callback() {
        remove_filter( 'widget_update_callback', 'rocket_widget_update_callback' );
    }
}
