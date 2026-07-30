<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Cache\Abilities;

use WP_Rocket\Engine\Abilities\Context as AbilitiesContext;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * ClearUrlCache ability instance.
	 *
	 * @var ClearUrlCache
	 */
	private $clear_url_cache;

	/**
	 * ClearWebsiteCache ability instance.
	 *
	 * @var ClearWebsiteCache
	 */
	private $clear_website_cache;

	/**
	 * Abilities context instance.
	 *
	 * @var AbilitiesContext
	 */
	private $abilities_context;

	/**
	 * Constructor.
	 *
	 * @param ClearUrlCache    $clear_url_cache               The ability to clear url cache.
	 * @param ClearWebsiteCache $clear_website_cache The ability to clear website cache.
	 * @param AbilitiesContext $abilities_context       The abilities context instance.
	 */
	public function __construct( ClearUrlCache $clear_url_cache, ClearWebsiteCache $clear_website_cache, AbilitiesContext $abilities_context ) {
		$this->clear_url_cache   = $clear_url_cache;
		$this->abilities_context = $abilities_context;
	}
	

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_abilities_api_init'            => 'register_clear_cache_ability',
			'wp_abilities_api_categories_init' => 'register_cache_category',
		];
	}

	/**
	 * Registers the ability to clear website/url cache.
	 */
	public function register_clear_cache_ability() {
		if ( ! $this->abilities_context->is_enabled() ) {
			return;
		}

		$this->clear_url_cache->register();
		$this->clear_website_cache->register();
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
