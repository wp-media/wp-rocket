<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DynamicLists;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * DynamicLists instance
	 *
	 * @var DynamicLists
	 */
	private $dynamic_lists;

	/**
	 * Instantiate the class
	 *
	 * @param DynamicLists $dynamic_lists DynamicLists instance.
	 */
	public function __construct( DynamicLists $dynamic_lists ) {
		$this->dynamic_lists = $dynamic_lists;
	}

	/**
	 * Events this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rest_api_init'                      => 'register_rest_route',
			'rocket_localize_admin_script'       => [ 'add_dynamic_lists_script', 11 ],
			'init'                               => 'schedule_lists_update',
			'rocket_update_dynamic_lists'        => 'update_lists',
			'rocket_deactivation'                => 'clear_schedule_lists_update',
			'rocket_settings_tools_content'      => 'display_update_lists_section',
			'rocket_cache_ignored_parameters'    => 'add_cache_ignored_parameters',
			'rocket_minify_excluded_external_js' => 'add_minify_excluded_external_js',
			'rocket_move_after_combine_js'       => 'add_move_after_combine_js',
			'rocket_excluded_inline_js_content'  => 'add_combine_js_excluded_inline',
		];
	}

	/**
	 * Registers the REST dynamic lists update route
	 *
	 * @return void
	 */
	public function register_rest_route() {
		$this->dynamic_lists->register_rest_route();
	}

	/**
	 * Add REST data to our localize script data.
	 *
	 * @param array $data Localize script data.
	 * @return array
	 */
	public function add_dynamic_lists_script( $data ) {
		$data['rest_url']   = rest_url( 'wp-rocket/v1/dynamic_lists/update/' );
		$data['rest_nonce'] = wp_create_nonce( 'wp_rest' );

		return $data;
	}

	/**
	 * Scheduling the dynamic lists update cron event.
	 */
	public function schedule_lists_update() {
		$this->dynamic_lists->schedule_lists_update();
	}

	/**
	 * Clear the dynamic lists update cron event.
	 *
	 *  @return void
	 */
	public function clear_schedule_lists_update() {
		$this->dynamic_lists->clear_schedule_lists_update();
	}

	/**
	 * Update dynamic lists from API.
	 *
	 * * @return void
	 */
	public function update_lists() {
		$this->dynamic_lists->update_lists_from_remote();
	}

	/**
	 * Displays the dynamic lists update section on tools tab
	 *
	 * @return void
	 */
	public function display_update_lists_section() {
		$this->dynamic_lists->display_update_lists_section();
	}

	/**
	 * Add the cached ignored parameters to the array
	 *
	 * @param string $params Array of ignored parameters.
	 *
	 * @return array
	 */
	public function add_cache_ignored_parameters( $params = [] ): array {
		if ( ! is_array( $params ) ) {
			$params = (array) $params;
		}

		return array_merge( $params, $this->dynamic_lists->get_cache_ignored_parameters() );
	}

	/**
	 * Add the excluded external JS patterns to the array
	 *
	 * @param string $excluded Array of excluded patterns.
	 *
	 * @return array
	 */
	public function add_minify_excluded_external_js( $excluded = [] ): array {
		if ( ! is_array( $excluded ) ) {
			$excluded = (array) $excluded;
		}

		return array_merge( $excluded, $this->dynamic_lists->get_js_minify_excluded_external() );
	}

	/**
	 * Add the JS patterns to move after the combine JS file to the array
	 *
	 * @param string $excluded Array of patterns to move.
	 *
	 * @return array
	 */
	public function add_move_after_combine_js( $excluded = [] ): array {
		if ( ! is_array( $excluded ) ) {
			$excluded = (array) $excluded;
		}

		return array_merge( $excluded, $this->dynamic_lists->get_js_move_after_combine() );
	}

	/**
	 * Add the excluded inline JS patterns to the array
	 *
	 * @param string $excluded Array of excluded patterns.
	 *
	 * @return array
	 */
	public function add_combine_js_excluded_inline( $excluded = [] ): array {
		if ( ! is_array( $excluded ) ) {
			$excluded = (array) $excluded;
		}

		return array_merge( $excluded, $this->dynamic_lists->get_combine_js_excluded_inline() );
	}
}
