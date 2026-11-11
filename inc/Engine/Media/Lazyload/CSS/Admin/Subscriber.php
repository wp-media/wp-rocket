<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Admin;

use WP_Rocket\Engine\Common\Cache\CacheInterface;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Cache instance.
	 *
	 * @var CacheInterface
	 */
	protected $cache;

	/**
	 * Instantiate class.
	 *
	 * @param CacheInterface $cache Cache instance.
	 */
	public function __construct( CacheInterface $cache ) {
		$this->cache = $cache;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_meta_boxes_fields'       => [ 'add_meta_box', 8 ],
			'admin_notices'                  => 'maybe_add_error_notice',
			'rocket_safe_mode_reset_options' => 'add_option_safemode',
			'wp_rocket_upgrade'              => [ 'clear_background_css_with_upgrade', 10, 2 ],
		];
	}

	/**
	 * Add the field to the WP Rocket metabox on the post edit page.
	 *
	 * @param string[] $fields Metaboxes fields.
	 *
	 * @return string[]
	 */
	public function add_meta_box( array $fields ) {
		$fields['lazyload_css_bg_img'] = __( 'LazyLoad CSS backgrounds', 'rocket' );
		return $fields;
	}

	/**
	 * Maybe display the error notice.
	 *
	 * @return void
	 */
	public function maybe_add_error_notice() {
		if ( ! current_user_can( 'rocket_manage_options' ) || $this->cache->is_accessible() ) {
			return;
		}

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => rocket_notice_writing_permissions( $this->cache->get_root_path() ),
			]
			);
	}

	/**
	 * Add option to safe mode.
	 *
	 * @param array $options Safe mode options.
	 *
	 * @return array
	 */
	public function add_option_safemode( array $options ) {
		$options['lazyload_css_bg_img'] = 0;
		return $options;
	}

	/**
	 * Upgrade callback.
	 *
	 * @param string $new_version Plugin new version.
	 * @param string $old_version Plugin old version.
	 * @return void
	 */
	public function clear_background_css_with_upgrade( $new_version, $old_version ) {
		if ( empty( $old_version ) || version_compare( $old_version, '3.18', '>' ) ) {
			return;
		}

		$preserve_dirs = is_multisite() ? get_sites( [ 'fields' => 'ids' ] ) : [ get_current_blog_id() ];

		// Completely clear background-css directory.
		$this->cache->full_clear( $preserve_dirs );
	}
}
