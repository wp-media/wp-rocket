<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Abilities;

use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Admin\RocketInsights\{
	Context\Context,
	Database\Queries\RocketInsights as Query
};

class RemovePageInsights implements AbilitiesInterface {
	/**
	 * Context instance providing necessary dependencies and configuration.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Database query class for interacting with Rocket Insights data.
	 *
	 * @var Query
	 */
	private $query;

	/**
	 * Constructor.
	 *
	 * @param Context $context The context instance providing necessary dependencies and configuration.
	 * @param Query   $query   The database query class for interacting with Rocket Insights data.
	 */
	public function __construct( Context $context, Query $query ) {
		$this->context = $context;
		$this->query   = $query;
	}

	/**
	 * Registers the ability to remove page insights.
	 */
	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wp-rocket/remove-page-insights',
			[
				'label'               => __( 'Remove Page Insights', 'rocket' ),
				'description'         => _x( 'Call when user wants to stop monitoring a page and delete its data. Always confirm before calling: state the URL and that results data will be permanently removed. Never call speculatively or in bulk without explicit per-page confirmation.', 'Ability description', 'rocket' ),
				'category'            => 'wp-rocket-insights',
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'url' => [
							'type'        => 'string',
							'format'      => 'uri',
							'description' => __( 'Full URL of the page to remove from Rocket Insights monitoring.', 'rocket' ),
						],
					],
					'required'   => [ 'url' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'success' => [
							'type'        => 'boolean',
							'description' => __( 'Indicates whether the page was successfully removed', 'rocket' ),
						],
						'error'   => [
							'type'        => 'string',
							'description' => __( 'Error message if the page could not be removed', 'rocket' ),
						],
					],
				],
				'execute_callback'    => [ $this, 'execute' ],
				'permission_callback' => [ $this, 'check_permissions' ],
				'meta'                => [
					'mcp'          => [
						'public' => true,
					],
					'show_in_rest' => true,
					'annotations'  => [
						'readonly'    => false,
						'destructive' => true,
						'idempotent'  => false,
					],
				],
			]
		);
	}

	/**
	 * Checks if the current user has permission to execute this ability.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'rocket_manage_options' );
	}

	/**
	 * Executes the ability to remove a page from Rocket Insights monitoring.
	 *
	 * @param array $input The input data containing the URL to be removed.
	 *
	 * @return array
	 */
	public function execute( $input = null ): array {
		if ( ! $this->context->is_allowed() ) {
			return [
				'success' => false,
				'error'   => 'Performance monitoring is disabled.',
			];
		}

		$url = rocket_add_url_protocol( $input['url'] );

		$rows = $this->query->get_rows_by_url( $url );

		if ( empty( $rows ) ) {
			return [
				'success' => false,
				'error'   => 'URL is not currently being monitored.',
			];
		}

		foreach ( $rows as $row ) {
			// Skip firing the deletion side-effects if the row was already gone (e.g. a concurrent delete).
			if ( ! $this->query->delete_item( $row->id ) ) {
				continue;
			}

			/**
			 * Fires when a performance monitoring job is deleted.
			 *
			 * @since 3.20
			 *
			 * @param int $id The ID of the deleted performance monitoring job.
			 */
			do_action( 'rocket_rocket_insights_job_deleted', $row->id );
		}

		return [
			'success' => true,
			'error'   => '',
		];
	}
}
