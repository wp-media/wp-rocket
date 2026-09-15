<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\Slider;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

/**
 * Compatibility with Soliloquy.
 */
class Soliloquy implements Subscriber_Interface, PluginCompatibilityInterface {
	/**
	 * Whether Soliloquy is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return rocket_has_constant( 'SOLILOQUY_VERSION' );
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'soliloquy_output_image_attr' => [ 'deactivate_lazyload', PHP_INT_MAX ],
			'soliloquy_indexable_images'  => [ 'deactivate_lazyload_indexable', PHP_INT_MAX ],
		];
	}

	/**
	 * Prevents LazyLoad from being applied to Soliloquy image attributes.
	 *
	 * @param string $attr Image attributes.
	 * @return string
	 */
	public function deactivate_lazyload( $attr ) {
		return $attr . ' data-no-lazy="1" ';
	}

	/**
	 * Prevents LazyLoad from being applied to Soliloquy indexable images.
	 *
	 * @param string $images Image HTML code.
	 * @return string
	 */
	public function deactivate_lazyload_indexable( $images ) {
		return str_replace( '<img', '<img data-no-lazy="1" ', $images );
	}
}
