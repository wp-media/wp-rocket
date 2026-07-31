<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Preload\Abilities;

use WP_Rocket\Engine\Abilities\Context as AbilitiesContext;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * CheckCacheStatus ability instance.
	 *
	 * @var CheckCacheStatus
	 */
	private $check_cache_status;

	/**
	 * CheckCacheHealth ability instance.
	 *
	 * @var CheckCacheHealth
	 */
	private $check_cache_health;

	/**
	 * PurgeCache ability instance.
	 *
	 * @var PurgeCache
	 */
	private $purge_cache;

	/**
	 * Abilities context instance.
	 *
	 * @var AbilitiesContext
	 */
	private $abilities_context;

	/**
	 * Constructor.
	 *
	 * @param CheckCacheStatus $check_cache_status The ability to get the cache status for a URL, post, or term.
	 * @param CheckCacheHealth $check_cache_health The ability to get a sitewide cache health summary.
	 * @param PurgeCache       $purge_cache        The ability to clear the cache.
	 * @param AbilitiesContext $abilities_context  The abilities context instance.
	 */
	public function __construct( CheckCacheStatus $check_cache_status, CheckCacheHealth $check_cache_health, PurgeCache $purge_cache, AbilitiesContext $abilities_context ) {
		$this->check_cache_status = $check_cache_status;
		$this->check_cache_health = $check_cache_health;
		$this->purge_cache        = $purge_cache;
		$this->abilities_context  = $abilities_context;
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_abilities_api_init' => [
				[ 'register_check_cache_status_ability' ],
				[ 'register_check_cache_health_ability' ],
				[ 'register_purge_cache_ability' ],
			],
		];
	}

	/**
	 * Registers the ability to get the cache status for a URL, post, or term.
	 *
	 * @return void
	 */
	public function register_check_cache_status_ability(): void {
		if ( ! $this->abilities_context->is_enabled() ) {
			return;
		}

		$this->check_cache_status->register();
	}

	/**
	 * Registers the ability to get a sitewide cache health summary.
	 *
	 * @return void
	 */
	public function register_check_cache_health_ability(): void {
		if ( ! $this->abilities_context->is_enabled() ) {
			return;
		}

		$this->check_cache_health->register();
	}

	/**
	 * Registers the ability to clear the cache.
	 *
	 * @return void
	 */
	public function register_purge_cache_ability(): void {
		if ( ! $this->abilities_context->is_enabled() ) {
			return;
		}

		$this->purge_cache->register();
	}
}
