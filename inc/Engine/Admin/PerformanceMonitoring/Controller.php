<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring as PMQuery;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Jobs\Manager;

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
	 * Constructor.
	 *
	 * @param PMQuery                      $query Query instance.
	 * @param Manager                      $manager Manager instance.
	 * @param PerformanceMonitoringContext $context Context instance.
	 */
	public function __construct( PMQuery $query, Manager $manager, PerformanceMonitoringContext $context ) {
		$this->query   = $query;
		$this->manager = $manager;
		$this->context = $context;
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
		$this->manager->add_url_to_the_queue( home_url(), true );
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
		}

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
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
	 * Get user data for the settings.
	 *
	 * @return array
	 */
	public function get_license__data(): array {
		return [
			'price'                 => '4.99',
			'currency'              => '$',
			'price_before_discount' => '9.99',
			'page_number'           => 10,
			'period'                => 'month',
		];
	}
}
