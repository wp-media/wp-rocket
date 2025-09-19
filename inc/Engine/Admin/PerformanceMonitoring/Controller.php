<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\{
	GlobalScore,
	Jobs\Manager,
	Context\PerformanceMonitoringContext,
	Database\Queries\PerformanceMonitoring as PMQuery,
	Credit\Manager as CreditManager
};
use WP_Rocket\Engine\License\API\User;

class Controller {
	/**
	 * Query object.
	 *
	 * @var PMQuery
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
	 * @var PerformanceMonitoringContext
	 */
	private $context;

	/**
	 * Credit manager instance.
	 *
	 * @var CreditManager
	 */
	private $credit_manager;

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
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param PMQuery                      $query Query instance.
	 * @param Manager                      $manager Manager instance.
	 * @param PerformanceMonitoringContext $context Context instance.
	 * @param CreditManager                $credit_manager Credit manager instance.
	 * @param GlobalScore                  $global_score GlobalScore instance.
	 * @param User                         $user User client API instance.
	 */
	public function __construct( PMQuery $query, Manager $manager, PerformanceMonitoringContext $context, CreditManager $credit_manager, GlobalScore $global_score, User $user ) {
		$this->query          = $query;
		$this->manager        = $manager;
		$this->context        = $context;
		$this->credit_manager = $credit_manager;
		$this->global_score   = $global_score;
		$this->user           = $user;
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
	 * @return void
	 */
	public function add_homepage() {
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		$url = home_url();

		$page_title = __( 'Home Page', 'rocket' );

		$this->manager->add_url_to_the_queue(
			$url,
			true,
			[
				'title' => $page_title,
			]
		);

		/**
		 * Fires when a performance monitoring job is added.
		 *
		 * @since 3.20
		 *
		 * @param string $url The URL that was added for monitoring.
		 */
		do_action( 'rocket_pm_job_added', home_url() );
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
			! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'delete_pm' )
		) {
			wp_nonce_ays( 'delete_pm' );
		}

		$id = ! empty( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		if ( ! empty( $id ) ) {
			$this->query->delete_item( $id );

			/**
			 * Fires when a performance monitoring job is deleted.
			 *
			 * @since 3.20
			 *
			 * @param int $id The ID of the deleted performance monitoring job.
			 */
			do_action( 'rocket_pm_job_deleted', $id );
		}

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
	}

	/**
	 * Adds homepage for monitoring from the dashboard widget.
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
			! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_pm_add_homepage' )
		) {
			wp_nonce_ays( 'rocket_pm_add_homepage' );
		}

		$this->add_homepage();

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
		$this->credit_manager->reset_credit();
	}

	/**
	 * Validate credit for DB row ID.
	 *
	 * @param int $row_id DB row ID.
	 *
	 * @return void
	 */
	public function validate_credit( $row_id ) {
		if ( $this->credit_manager->decrease_credit() ) {
			return;
		}

		$this->query->make_blurred( $row_id );
	}

	/**
	 * Get settings section data.
	 *
	 * @return array
	 */
	public function get_settings_section_data(): array {
		return [
			'id'                 => 'performance_monitoring',
			'title'              => __( 'Performance Monitoring', 'rocket' ),
			'value'              => 1, // enabled or not.
			'schedule_frequency' => 'weekly', // frequency of tests.
			'choices'            => [ // frequency options in select.
				'daily'   => __( 'Daily', 'rocket' ),
				'weekly'  => __( 'Weekly', 'rocket' ),
				'monthly' => __( 'Monthly', 'rocket' ),
			],
			'help'               => 'performance-monitoring-settings', // beacon id for help button.
		];
	}

	/**
	 * Retrieves the current credit available for performance monitoring.
	 *
	 * @return int The current credit value.
	 */
	public function get_current_credit() {
		return $this->credit_manager->get_credit();
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
		$upgrades = $this->user->get_pma_addon_upgrade_skus( $this->user->get_pma_addon_sku_active() );
		return ! empty( $upgrades );
	}

	/**
	 * Get user data for the settings.
	 *
	 * @return array
	 */
	public function get_license_data(): array {
		$sku      = $this->user->get_pma_addon_sku_active();
		$upgrades = $this->user->get_pma_addon_upgrade_skus( $sku );
		$upgrade  = array_shift( $upgrades );

		$price = $this->user->get_pma_addon_price( $upgrade );

		$limit = $this->user->get_pma_addon_limit( $upgrade );

		$data            = [
			'currency'    => '$',
			'page_number' => $limit,
			'period'      => 'month',
			'description' => $this->user->get_pma_addon_description( $upgrade ),
			'highlights'  => $this->user->get_pma_addon_highlights( $upgrade ),
		];
		$data['btn_url'] = $this->user->get_pma_addon_btn_url( $upgrade );

		if ( ! $this->user->has_pma_addon_promo( $upgrade ) ) {
			$data['price']                 = $price;
			$data['price_before_discount'] = '';

			return $data;
		}

		$promo_price                   = $this->user->get_pma_addon_promo_price( $upgrade );
		$data['price']                 = $promo_price;
		$data['price_before_discount'] = $price;
		$data['promo_name']            = $this->user->get_pma_addon_promo_name( $upgrade );
		$data['promo_description']     = $this->user->get_pma_addon_promo_description( $upgrade );
		return $data;
	}
}
