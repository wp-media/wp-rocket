<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DeferJS;

use WP_Rocket\Event_Management\Subscriber_Interface;

class AdminSubscriber implements Subscriber_Interface {
	/**
	 * DeferJS instance
	 *
	 * @var DeferJS
	 */
	private $defer_js;

	/**
	 * Instantiate the class
	 *
	 * @param DeferJS $defer_js DeferJS instance.
	 */
	public function __construct( DeferJS $defer_js ) {
		$this->defer_js = $defer_js;
	}

	/**
	 * Returns array of events this listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_first_install_options' => 'add_defer_js_option',
			'wp_rocket_upgrade'            => [ 'exclude_jquery_defer', 14, 2 ],
			'rocket_meta_boxes_fields'     => [ 'add_meta_box', 5 ],
		];
	}

	/**
	 * Adds defer js option to WP Rocket options array
	 *
	 * @since 3.8
	 *
	 * @param array $options WP Rocket options array.
	 * @return array
	 */
	public function add_defer_js_option( array $options ): array {
		return $this->defer_js->add_option( $options );
	}

	/**
	 * Adds jQuery to defer JS exclusion field if safe mode was enabled before 3.8
	 *
	 * @since 3.8
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function exclude_jquery_defer( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.8', '>' ) ) {
			return;
		}

		$this->defer_js->exclude_jquery_upgrade();
	}

	/**
	 * Add the field to the WP Rocket metabox on the post edit page.
	 *
	 * @param string[] $fields Metaboxes fields.
	 *
	 * @return string[]
	 */
	public function add_meta_box( array $fields ) {
		$fields['defer_all_js'] = __( 'Load JavaScript deferred', 'rocket' );

		return $fields;
	}
}
