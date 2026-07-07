<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Cache\Abilities;

use WP_Rocket\Engine\Abilities\Context as AbilitiesContext;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * PurgeUrl ability instance.
	 *
	 * @var PurgeUrl
	 */
	private $purge_url;

	/**
	 * Abilities context instance.
	 *
	 * @var AbilitiesContext
	 */
	private $abilities_context;

	/**
	 * Constructor.
	 *
	 * @param PurgeUrl         $purge_url               The ability to purge url cache.
	 * @param AbilitiesContext $abilities_context       The abilities context instance.
	 */
	public function __construct( PurgeUrl $purge_url, AbilitiesContext $abilities_context ) {
		$this->purge_url         = $purge_url;
		$this->abilities_context = $abilities_context;
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_abilities_api_init'            => 'register_purge_url_ability',
			'wp_abilities_api_categories_init' => 'register_cache_category',
		];
	}

	/**
	 * Registers the ability to purge url cache.
	 */
	public function register_purge_url_ability() {
		if ( ! $this->abilities_context->is_enabled() ) {
			return;
		}

		$this->purge_url->register();
	}

	/**
	 * Registers WP Rocket Cache ability category.
	 */
	public function register_cache_category() {
		if ( ! $this->abilities_context->is_enabled() ) {
			return;
		}

		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category(
			'wp-rocket-cache',
			[
				'label'       => __( 'WP Rocket Cache', 'rocket' ),
				'description' => __( 'Abilities related to WP Rocket Cache.', 'rocket' ),
			]
		);
	}
}
