<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreconnectExternalDomains\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;

class Subscriber implements Subscriber_Interface {
	/**
	 * Controller instance.
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * DataManager instance
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Constructor.
	 *
	 * @param Controller  $controller Controller instance.
	 * @param DataManager $data_manager DataManager instance.
	 */
	public function __construct( Controller $controller, DataManager $data_manager ) {
		$this->controller   = $controller;
		$this->data_manager = $data_manager;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_head_items'                     => [ 'preconnect_domains', 10 ],
			'rocket_cdn_insert_resource_hints'      => 'stop_cdn_insert_resource_hints',
			'preconnect_external_domain_exclusions' => 'get_exclusions',
		];
	}

	/**
	 * Preconnect current page domains into head.
	 *
	 * @param array $items Head items.
	 * @return array
	 */
	public function preconnect_domains( array $items ) {
		return $this->controller->add_preconnect_to_head( $items );
	}

	/**
	 * Stop CDN from adding resource hints into head.
	 *
	 * @param bool $status Current status.
	 * @return bool
	 */
	public function stop_cdn_insert_resource_hints( $status ): bool {
		return $this->controller->can_cdn_insert_resource_hints( $status );
	}

	/** Gets the exclusion patterns used to identify elements that should be excluded.
	 * Merges any existing exclusions pattern with those from the dynamic lists.
	 *
	 * @param array $exclusions Array of exclusion patterns.
	 * @return array
	 */
	public function get_exclusions( array $exclusions ): array {
		$lists = $this->data_manager->get_lists()->preconnect_external_domains_exclusions ?? [];
		/**
		 * Merge exclusions and lists.
		 * Handle empty arrays gracefully.
		 */
		return array_merge( $exclusions, (array) $lists );
	}
}
