<?php

namespace WP_Rocket\Engine\Cache;

use WP_Filesystem_Direct;
use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Event_Management\Event_Manager_Aware_Subscriber_Interface;

/**
 * Subscriber for the cache admin events
 *
 * @since 3.5.5
 */
class AdminSubscriber implements Event_Manager_Aware_Subscriber_Interface {
	/**
	 * Event Manager instance
	 *
	 * @var Event_Manager;
	 */
	protected $event_manager;

	/**
	 * AdvancedCache instance
	 *
	 * @var AdvancedCache
	 */
	private $advanced_cache;

	/**
	 * WPCache instance
	 *
	 * @var WPCache
	 */
	private $wp_cache;

	/**
	 * WordPress filesystem.
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * Instantiate the class
	 *
	 * @param AdvancedCache             $advanced_cache AdvancedCache instance.
	 * @param WPCache                   $wp_cache WPCache instance.
	 * @param WP_Filesystem_Direct|null $filesystem WordPress filesystem.
	 */
	public function __construct( AdvancedCache $advanced_cache, WPCache $wp_cache, $filesystem = null ) {
		$this->advanced_cache = $advanced_cache;
		$this->wp_cache       = $wp_cache;
		$this->filesystem     = ! empty( $filesystem ) ? $filesystem : rocket_direct_filesystem();
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$slug = rocket_get_constant( 'WP_ROCKET_SLUG' );
		return [
			'admin_init'            => [
				[ 'register_terms_row_action' ],
				[ 'maybe_set_wp_cache' ],
			],
			'admin_notices'         => [
				[ 'notice_advanced_cache_permissions' ],
				[ 'notice_wp_config_permissions' ],
			],
			"update_option_{$slug}" => [ 'maybe_set_wp_cache', 12 ],
			'site_status_tests'     => 'add_wp_cache_status_test',
			'wp_rocket_upgrade'     => [ 'on_update', 10, 2 ],
			'rocket_domain_changed' => [
				[ 'regenerate_configs' ],
				[ 'delete_old_configs' ],
				[ 'clear_cache', 10, 2 ],
			],
		];
	}

	/**
	 * Sets the event manager for the subscriber.
	 *
	 * @param Event_Manager $event_manager Event Manager instance.
	 */
	public function set_event_manager( Event_Manager $event_manager ) {
		$this->event_manager = $event_manager;
	}

	/**
	 * Registers the action for each public taxonomy
	 *
	 * @since 3.5.5
	 *
	 * @return void
	 */
	public function register_terms_row_action() {
		$taxonomies = get_taxonomies(
			[
				'public'             => true,
				'publicly_queryable' => true,
			]
		);

		foreach ( $taxonomies as $taxonomy ) {
			$this->event_manager->add_callback( "{$taxonomy}_row_actions", [ $this, 'add_purge_term_link' ], 10, 2 );
		}
	}

	/**
	 * Adds a link "Purge this cache" in the terms list table
	 *
	 * @param array   $actions An array of action links to be displayed.
	 * @param WP_Term $term Term object.
	 *
	 * @return array
	 */
	public function add_purge_term_link( $actions, $term ) {
		if ( ! current_user_can( 'rocket_purge_terms' ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			admin_url( "admin-post.php?action=purge_cache&type=term-{$term->term_id}&taxonomy={$term->taxonomy}" ),
			"purge_cache_term-{$term->term_id}"
		);

		$actions['rocket_purge'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			$url,
			__( 'Clear this cache', 'rocket' )
		);

		return $actions;
	}

	/**
	 * Displays the notice for advanced-cache.php permissions
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function notice_advanced_cache_permissions() {
		$this->advanced_cache->notice_permissions();
	}

	/**
	 * Set WP_CACHE constant to true if needed
	 *
	 * @since 3.6.1
	 *
	 * @return void
	 */
	public function maybe_set_wp_cache() {
		$this->wp_cache->maybe_set_wp_cache();
	}

	/**
	 * Displays the notice for wp-config.php permissions
	 *
	 * @since 3.6.1
	 *
	 * @return void
	 */
	public function notice_wp_config_permissions() {
		$this->wp_cache->notice_wp_config_permissions();
	}

	/**
	 * Adds a Site Health check for the WP_CACHE constant value
	 *
	 * @since 3.6.1
	 *
	 * @param array $tests An array of tests to perform.
	 * @return array
	 */
	public function add_wp_cache_status_test( $tests ) {
		return $this->wp_cache->add_wp_cache_status_test( $tests );
	}

	/**
	 * Regenerate configs.
	 *
	 * @return void
	 */
	public function regenerate_configs() {
		rocket_generate_advanced_cache_file();
		flush_rocket_htaccess();
		rocket_generate_config_file();
	}

	/**
	 * Delete old config files.
	 *
	 * @return void
	 */
	public function delete_old_configs() {
		$configs = [];

		if ( is_multisite() ) {
			foreach ( get_sites( [ 'fields' => 'ids' ] ) as $site_id ) {
				switch_to_blog( $site_id );
				$configs[] = $this->generate_config_path();
				restore_current_blog();
			}
		} else {
			$configs[] = $this->generate_config_path();
		}

		$contents = $this->filesystem->dirlist( WP_ROCKET_CONFIG_PATH );
		foreach ( $contents as $content ) {
			$content = WP_ROCKET_CONFIG_PATH . $content['name'];
			if ( ! preg_match( '#\.php$#', $content ) || ! $this->filesystem->is_file( $content ) || in_array(
					$content,
					$configs,
					true
				) ) {
				continue;
			}

			if ( false === strpos( $this->filesystem->get_contents( $content ), '$rocket_cookie_hash' ) ) {
				continue;
			}

			$this->filesystem->delete( $content );
		}
	}

	/**
	 * Generate the path to the config for the current website.
	 *
	 * @return string
	 */
	protected function generate_config_path() {
		$file         = get_rocket_parse_url( untrailingslashit( home_url() ) );
		$file['path'] = ( ! empty( $file['path'] ) ) ? str_replace( '/', '.', untrailingslashit( $file['path'] ) ) : '';
		return WP_ROCKET_CONFIG_PATH . strtolower( $file['host'] ) . $file['path'] . '.php';
	}

	/**
	 * Clear cache.
	 *
	 * @param string $current_url current URL from the website.
	 * @param string $old_url old URL from the website.
	 *
	 * @return void
	 */
	public function clear_cache( string $current_url, string $old_url ) {
		rocket_clean_files( [ $old_url, $current_url ], null, false );
	}

	/**
	 * Regenerate the advanced cache file on update
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function on_update( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.15', '>=' ) ) {
			return;
		}
		rocket_generate_advanced_cache_file();
	}
}
