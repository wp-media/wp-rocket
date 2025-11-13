<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights;

use WP_Rocket\Engine\Admin\RocketInsights\{GlobalScore,
	Jobs\Manager,
	Context\Context,
	Database\Queries\RocketInsights as Query,
	Managers\Plan
};
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\User;

class Controller {
	/**
	 * Query object.
	 *
	 * @var Query
	 */
	private $query;

	/**
	 * Manager instance.
	 *
	 * @var Manager
	 */
	private $manager;

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Plan instance.
	 *
	 * @var Plan
	 */
	private $plan;

	/**
	 * GlobalScore instance.
	 *
	 * @var GlobalScore
	 */
	private $global_score;

	/**
	 * User client API instance.
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Constructor.
	 *
	 * @param Query        $query Query instance.
	 * @param Manager      $manager Manager instance.
	 * @param Context      $context Context instance.
	 * @param Plan         $plan Plan instance.
	 * @param GlobalScore  $global_score GlobalScore instance.
	 * @param User         $user User client API instance.
	 * @param Options_Data $options Plugin options instance.
	 */
	public function __construct(
		Query $query,
		Manager $manager,
		Context $context,
		Plan $plan,
		GlobalScore $global_score,
		User $user,
		Options_Data $options
	) {
		$this->query        = $query;
		$this->manager      = $manager;
		$this->context      = $context;
		$this->plan         = $plan;
		$this->global_score = $global_score;
		$this->user         = $user;
		$this->options      = $options;
	}

	/**
	 * Get items from the database.
	 *
	 * @return array|int
	 */
	public function get_items() {
		$query_params = [
			'orderby' => 'modified',
			'order'   => 'asc',
			'number'  => 20,
		];
		return $this->query->query( $query_params );
	}

	/**
	 * Add homepage to the database to be queued.
	 *
	 * @param string $source The source of the request. Default 'auto-added homepage'.
	 * @return void
	 */
	public function add_homepage( $source = 'auto-added homepage' ) {
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		$url = home_url();

		$page_title = __( 'Homepage', 'rocket' );

		$this->manager->add_to_the_queue(
			$url,
			true,
			[
				'title' => $page_title,
				'data'  => [
					'source' => $source,
				],
			]
		);

		$urls_count   = $this->query->get_total_count();
		$current_plan = $this->plan->get_current_plan();

		/**
		 * Fires when a Rocket Insights job is added.
		 *
		 * @since 3.20
		 *
		 * @param string $url          The URL that was added for monitoring.
		 * @param string $current_plan The current plan of the user.
		 * @param int    $urls_count   The current number of URLs being monitored.
		 * @param string $source       The source of the request.
		 */
		do_action( 'rocket_rocket_insights_job_added', $url, $current_plan, $urls_count, $source );
	}

	/**
	 * Get not finished IDs.
	 *
	 * @return array
	 */
	public function get_not_finished_ids() {
		return $this->query->get_not_finished_ids();
	}

	/**
	 * Delete one row.
	 *
	 * @return void
	 */
	public function delete_row() {
		if ( ! $this->context->is_allowed() ) {
			wp_die();
		}

		if (
			! isset( $_GET['_wpnonce'] )
			||
			! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'delete_rocket_insights_url' )
		) {
			wp_nonce_ays( 'delete_rocket_insights_url' );
		}

		$id = ! empty( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		if ( ! empty( $id ) ) {
			$this->query->delete_item( $id );

			/**
			 * Fires when a Rocket Insights job is deleted.
			 *
			 * @since 3.20
			 *
			 * @param int $id The ID of the deleted Rocket Insights job.
			 */
			do_action( 'rocket_rocket_insights_job_deleted', $id );
		}

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
	}

	/**
	 * Adds homepage to Rocket Insights from the dashboard widget.
	 *
	 * @return void
	 */
	public function add_homepage_from_widget() {
		if ( ! $this->context->is_allowed() ) {
			wp_die();
		}

		if (
			! isset( $_GET['_wpnonce'] )
			||
			! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_rocket_insights_add_homepage' )
		) {
			wp_nonce_ays( 'rocket_rocket_insights_add_homepage' );
		}

		$this->add_homepage( 'dashboard' );

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
	}

	/**
	 * Get global score data.
	 *
	 * @return array
	 */
	public function get_global_score() {
		return $this->global_score->get_global_score_data();
	}

	/**
	 * Reset credit.
	 *
	 * @return void
	 */
	public function reset_credit() {
		$this->plan->reset_credit();
	}

	/**
	 * Validate credit for DB row ID.
	 *
	 * @param int $row_id DB row ID.
	 *
	 * @return void
	 */
	public function validate_credit( $row_id ) {
		if ( $this->plan->decrease_credit() ) {
			return;
		}

		$this->query->make_blurred( $row_id );
	}

	/**
	 * Retrieves the current credit available for performance monitoring.
	 *
	 * @return bool If there is credit or not.
	 */
	public function has_credit() {
		return $this->plan->has_credit();
	}

	/**
	 * Display banner.
	 *
	 * @return bool
	 */
	public function display_banner(): bool {
		if ( ! $this->context->is_allowed() ) {
			return false;
		}

		$upgrades = $this->user->get_rocket_insights_addon_upgrade_skus( $this->user->get_rocket_insights_addon_sku_active() );

		return ! empty( $upgrades );
	}

	/**
	 * Get user data for the settings.
	 *
	 * @return array
	 */
	public function get_license_data(): array {
		$sku      = $this->user->get_rocket_insights_addon_sku_active();
		$upgrades = $this->user->get_rocket_insights_addon_upgrade_skus( $sku );

		if ( empty( $upgrades ) ) {
			return [];
		}

		$upgrade = array_shift( $upgrades );
		$price   = $this->user->get_rocket_insights_addon_price( $upgrade );

		$limit = $this->user->get_rocket_insights_addon_limit( $upgrade );

		$data            = [
			'currency'    => '$',
			'page_number' => $limit,
			'period'      => __( 'month', 'rocket' ),
			'subtitle'    => $this->user->get_rocket_insights_addon_subtitle( $upgrade ),
			'billing'     => $this->user->get_rocket_insights_addon_billing( $upgrade ),
			'highlights'  => $this->user->get_rocket_insights_addon_highlights( $upgrade ),
		];
		$data['btn_url'] = $this->user->get_rocket_insights_addon_btn_url( $upgrade );

		if ( ! $this->user->has_rocket_insights_addon_promo( $upgrade ) ) {
			$data['price']                 = $price;
			$data['price_before_discount'] = '';

			return $data;
		}

		$promo_price                   = $this->user->get_rocket_insights_addon_promo_price( $upgrade );
		$data['price']                 = $promo_price;
		$data['price_before_discount'] = $price;
		$data['promo_name']            = $this->user->get_rocket_insights_addon_promo_name( $upgrade );
		$data['promo_billing']         = $this->user->get_rocket_insights_addon_promo_billing( $upgrade );
		return $data;
	}

	/**
	 * Get the remaining number of URLs that can be added based on user's plan limit.
	 *
	 * @return int Number of URLs that can still be added.
	 */
	public function get_remaining_url_count(): int {
		$current_url_count = $this->query->get_total_count();
		$max_urls          = $this->user->get_rocket_insights_addon_limit( $this->user->get_rocket_insights_addon_sku_active() );

		return max( 0, $max_urls - (int) $current_url_count );
	}

	/**
	 * Get the total count of URLs in Rocket Insights.
	 *
	 * @return int Total number of URLs being monitored.
	 */
	public function get_total_url_count(): int {
		return $this->query->get_total_count();
	}

	/**
	 * Get Rocket Insights addon limit.
	 *
	 * @return int
	 */
	public function get_rocket_insights_addon_limit() {
		return $this->user->get_rocket_insights_addon_limit( $this->user->get_rocket_insights_addon_sku_active() );
	}

	/**
	 * Maybe show upgrade notice.
	 *
	 * @return void
	 */
	public function maybe_show_notice() {
		if ( ! $this->context->is_allowed() || $this->context->is_free_user() ) {
			return;
		}

		if (
			in_array(
				'insights_upgrade',
				(array) get_user_meta( get_current_user_id(), 'rocket_boxes', true ),
				true
			)
		) {
			return;
		}

		rocket_notice_html(
			[
				'status'                 => 'rocket_insights wpr-ri-notice',
				'dismissible'            => 'is-dismissible',
				'message'                => sprintf(
				// Translators: %1$s = opening strong tag, %2$s = closing strong tag, %3$s = number of pages as a limit.
					esc_html__( '%1$sCongrats!%2$s You can now monitor up to %3$s pages, run unlimited on-demand tests, and schedule them to run automatically.', 'rocket' ),
					'<strong>',
					'</strong>',
					$this->get_rocket_insights_addon_limit()
				),
				'id'                     => 'insights_upgrade',
				'class_prefix'           => 'wpr-',
				'dismiss_button'         => 'insights_upgrade',
				'dismiss_button_class'   => 'wpr-notice-close wpr-icon-close rocket-dismiss',
				'dismiss_button_message' => '',
			]
		);
	}

	/**
	 * Maybe show notice for paid users when reaching limits.
	 *
	 * @return void
	 */
	public function maybe_show_paid_reach_limits_notice() {
		if ( ! $this->context->is_allowed() || $this->context->is_free_user() ) {
			return;
		}

		rocket_notice_html(
			[
				'status'       => 'rocket_insights wpr-ri-notice' . ( 0 < $this->get_remaining_url_count() ? ' hidden' : '' ),
				'message'      => sprintf(
				// Translators: %1$s = number of pages as a limit, %2$s anchor tag opening, %3$s anchor tag closing.
					esc_html__( 'Wow, you’ve already added %1$s pages! That\'s the limit for now. Help shape what’s next by %2$ssharing your feedback%3$s.', 'rocket' ),
					$this->get_rocket_insights_addon_limit(),
					'<a href="https://wp-rocket.me/rocket-insights-survey/" rel="noopener noreferrer" target="_blank">',
					'</a>'
				),
				'id'           => 'rocket_insights_survey',
				'class_prefix' => 'wpr-',
				'dismissible'  => '',
			]
		);
	}
}
