<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Frontend Controller instance.
	 *
	 * @var Controller
	 */
	private $frontend_controller;

	/**
	 * Constructor.
	 *
	 * @param Controller $frontend_controller Frontend Controller instance.
	 */
	public function __construct( Controller $frontend_controller ) {
		$this->frontend_controller = $frontend_controller;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.18
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_buffer'                       => [ 'rewrite_fonts_for_optimizations', 18 ],
			'rocket_disable_google_fonts_preload' => 'disable_google_fonts_preload',
			'rocket_performance_hints_buffer'     => 'rewrite_fonts_for_saas',
			'rocket_head_items'                   => [ 'rewrite_fonts_in_head', 1000 ],
		];
	}

	/**
	 * Rewrites the Google Fonts paths to local ones.
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function rewrite_fonts_for_optimizations( $html ): string {
		return $this->frontend_controller->rewrite_fonts_for_optimizations( $html );
	}

	/**
	 * Rewrites the Google Fonts paths to local ones for SaaS.
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function rewrite_fonts_for_saas( $html ): string {
		return $this->frontend_controller->rewrite_fonts_for_saas( $html );
	}

	/**
	 * Disables the preload of Google Fonts.
	 *
	 * @param bool $disable Whether to disable the preload of Google Fonts.
	 *
	 * @return bool
	 */
	public function disable_google_fonts_preload( $disable ): bool {
		return $this->frontend_controller->disable_google_fonts_preload( $disable );
	}

	/**
	 * Rewrite all google fonts found in head elements.
	 *
	 * @param array $items Head items.
	 * @return array
	 */
	public function rewrite_fonts_in_head( $items ): array {
		return $this->frontend_controller->rewrite_fonts_in_head( $items );
	}
}
