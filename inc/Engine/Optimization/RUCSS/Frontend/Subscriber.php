<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Frontend;

use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Context\RUCSSContext;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

class Subscriber implements Subscriber_Interface {

	/**
	 * UsedCss instance
	 *
	 * @var UsedCSS
	 */
	private $used_css;

	/**
	 * RUCSS context.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * Instantiate the class
	 *
	 * @param UsedCSS          $used_css UsedCSS instance.
	 * @param ContextInterface $context RUCSS context.
	 */
	public function __construct( UsedCSS $used_css, ContextInterface $context ) {
		$this->used_css = $used_css;
		$this->context  = $context;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_buffer'                => [ 'treeshake', 1000 ],
			'rocket_first_install_options' => 'on_install',
			'wp_rocket_upgrade'            => [ 'on_update', 10, 2 ],
			'rocket_head_items'            => [
				[ 'insert_preload_fonts', 1100 ],
				[ 'insert_css_in_head', 1200 ],
			],
		];
	}

	/**
	 * Apply TreeShaked CSS to the current HTML page.
	 *
	 * @param string $html  HTML content.
	 *
	 * @return string  HTML content.
	 */
	public function treeshake( string $html ): string {
		return $this->used_css->treeshake( $html );
	}

	/**
	 * Add option on update.
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

		$default = 0;

		if ( get_transient( 'wp_rocket_no_licence' ) ) {
			$default = get_transient( 'wp_rocket_no_licence' );
			delete_transient( 'wp_rocket_no_licence' );
		}

		update_option( 'wp_rocket_no_licence', $default );
	}

	/**
	 * Add option on installation.
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function on_install( $options ) {

		update_option( 'wp_rocket_no_licence', 0 );

		return $options;
	}

	/**
	 * Insert used CSS into head.
	 *
	 * @param array $items Head elements.
	 * @return mixed
	 */
	public function insert_css_in_head( $items ) {
		return $this->used_css->add_used_css_to_html( $items );
	}

	/**
	 * Insert font preloads into head.
	 *
	 * @param array $items Head elements.
	 * @return mixed
	 */
	public function insert_preload_fonts( $items ) {
		return $this->used_css->insert_preload_fonts( $items );
	}
}
