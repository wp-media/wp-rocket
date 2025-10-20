<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\URLLimit;

use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;

class Subscriber implements Subscriber_Interface {
	/**
	 * Rocket Insights query instance.
	 *
	 * @var Query
	 */
	private $query;

	/**
	 * User client API instance.
	 *
	 * @var User
	 */
	private $user;

	/**
	 * GlobalScore instance.
	 *
	 * @var GlobalScore
	 */
	private $global_score;

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor
	 *
	 * @param Query       $query        Rocket Insights query instance.
	 * @param User        $user         User client API instance.
	 * @param GlobalScore $global_score GlobalScore instance.
	 * @param Context     $context      Context instance.
	 */
	public function __construct( Query $query, User $user, GlobalScore $global_score, Context $context ) {
		$this->query        = $query;
		$this->user         = $user;
		$this->global_score = $global_score;
		$this->context      = $context;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_rocket_insights_allow_add_page' => 'is_adding_page_allowed',
			'rocket_rocket_insights_upgrade'        => [
				[ 'clean_upgrade_plan_urls', 10, 2 ],
				[ 'unblur_rows', 11 ],
			],
		];
	}

	/**
	 * Checks if adding a new page is allowed based on user license and current URL count.
	 *
	 * @return bool True if adding a page is allowed, false otherwise.
	 */
	public function is_adding_page_allowed(): bool {
		$max_urls = $this->user->get_rocket_insights_addon_limit( $this->user->get_rocket_insights_addon_sku_active() );

		return $this->query->get_total_count() < $max_urls;
	}

	/**
	 * Make sure that the new plan limits on urls are applied.
	 *
	 * @param string $old_plan Old plan sku.
	 * @param string $new_plan New plan sku.
	 *
	 * @return void
	 */
	public function clean_upgrade_plan_urls( $old_plan, $new_plan ) {
		$limit = $this->user->get_rocket_insights_addon_limit( $new_plan );

		if ( $this->query->get_total_count() <= $limit ) {
			return;
		}

		$this->query->prune_old_items( $limit );
		$this->global_score->reset();
	}

	/**
	 * Change blurred rows into unblurred.
	 *
	 * @return void
	 */
	public function unblur_rows() {
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		if ( $this->context->is_free_user() ) {
			return;
		}

		$this->query->unblur_rows();
		$this->global_score->reset();
	}
}
