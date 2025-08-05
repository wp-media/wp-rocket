<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DynamicLists;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\License\API\User;
use WP_REST_Response;
use WP_Error;

class DynamicLists extends Abstract_Render {

	/**
	 * Providers array.
	 * Array of objects with keys: api_client and data_manager.
	 *
	 * @var array
	 */
	private $providers;

	/**
	 * User instance
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Beacon instance
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * Route Rest API namespace.
	 */
	const ROUTE_NAMESPACE = 'wp-rocket/v1';

	/**
	 * Instantiate the class.
	 *
	 * @param array  $providers Lists providers.
	 * @param User   $user User instance.
	 * @param string $template_path Path to views.
	 * @param Beacon $beacon        Beacon instance.
	 */
	public function __construct( array $providers, User $user, $template_path, Beacon $beacon ) {
		parent::__construct( $template_path );

		$this->providers = $providers;
		$this->user      = $user;
		$this->beacon    = $beacon;
	}

	/**
	 * Registers the dynamic lists update route
	 *
	 * @return void
	 */
	public function register_rest_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'dynamic_lists/update',
			[
				'methods'             => 'PUT',
				'callback'            => [ $this, 'rest_update_response' ],
				'permission_callback' => [ $this, 'check_permissions' ],
			]
		);
	}
	/**
	 * Checks user's permissions. This is a callback registered to REST route's "permission_callback" parameter.
	 *
	 * @return bool true if the user has permission; else false.
	 */
	public function check_permissions() {
		return current_user_can( 'rocket_manage_options' );
	}

	/**
	 * Returns the update response
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function rest_update_response() {
		return rest_ensure_response( $this->update_lists_from_remote() );
	}

	/**
	 * Updates the lists from remote
	 *
	 * @return array
	 */
	public function update_lists_from_remote() {
		if ( $this->user->is_license_expired() ) {
			return [
				'success' => false,
				'data'    => '',
				'message' => __( 'You need an active license to get the latest version of the lists from our server.', 'rocket' ),
			];
		}

		$response     = [];
		$success      = false;
		$should_purge = false;
		$titles       = [
			'defaultlists'         => __( 'Default Lists', 'rocket' ),
			'delayjslists'         => __( 'Delay JavaScript Execution Exclusion Lists', 'rocket' ),
			'incompatible_plugins' => __( 'Incompatible plugins Lists', 'rocket' ),
		];

		foreach ( $this->providers as $provider_id => $provider ) {
			$result = $provider->api_client->get_exclusions_list( $provider->data_manager->get_lists_hash() );

			if ( empty( $result['code'] ) || empty( $result['body'] ) ) {
				$response[ $titles[ $provider_id ] ] = [
					'success' => false,
					'data'    => '',
					'message' => __( 'Could not get updated lists from server.', 'rocket' ),
				];
				continue;
			}

			if ( 206 === $result['code'] ) {
				$response[ $titles[ $provider_id ] ] = [
					'success' => true,
					'data'    => '',
					'message' => __( 'Lists are up to date.', 'rocket' ),
				];
				continue;
			}

			if ( ! $provider->data_manager->save_dynamic_lists( $result['body'] ) ) {
				$response[ $titles[ $provider_id ] ] = [
					'success' => false,
					'data'    => '',
					'message' => __( 'Could not update lists.', 'rocket' ),
				];
				continue;
			}

			$success = true;

			$response[ $titles[ $provider_id ] ] = [
				'success' => true,
				'data'    => '',
				'message' => __( 'Lists are successfully updated.', 'rocket' ),
			];

			$should_purge |= $provider->clear_cache ?? true;
		}

		if ( $success ) {
			/**
			 * Fires after saving all dynamic lists files.
			 *
			 * @since 3.12.1
			 *
			 * @param bool $should_purge Should purge status based on the updated providers.
			 */
			do_action( 'rocket_after_save_dynamic_lists', $should_purge );
		}

		return $response;
	}

	/**
	 * Schedule cron to update dynamic lists weekly.
	 *
	 * @return void
	 */
	public function schedule_lists_update() {
		if ( ! wp_next_scheduled( 'rocket_update_dynamic_lists' ) ) {
			wp_schedule_event( time(), 'weekly', 'rocket_update_dynamic_lists' );
		}
	}

	/**
	 * Clear dynamic lists update event.
	 */
	public function clear_schedule_lists_update() {
		wp_clear_scheduled_hook( 'rocket_update_dynamic_lists' );
	}

	/**
	 * Displays the dynamic lists update section on tools tab
	 *
	 * @return void
	 */
	public function display_update_lists_section() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$data = [
			'beacon' => $this->beacon->get_suggest( 'dynamic_lists' ),
		];

		echo $this->generate( 'settings/dynamic-lists-update', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get the cached ignored parameters
	 *
	 * @return array
	 */
	public function get_cache_ignored_parameters(): array {
		$lists = $this->providers['defaultlists']->data_manager->get_lists();

		return isset( $lists->cache_ignored_parameters ) ? array_flip( $lists->cache_ignored_parameters ) : [];
	}

	/**
	 * Get the JS minify excluded external paths
	 *
	 * @return array
	 */
	public function get_js_minify_excluded_external(): array {
		$lists = $this->providers['defaultlists']->data_manager->get_lists();

		return isset( $lists->js_minify_external ) ? $lists->js_minify_external : [];
	}

	/**
	 * Get the patterns to move after the combine JS file
	 *
	 * @return array
	 */
	public function get_js_move_after_combine(): array {
		$lists = $this->providers['defaultlists']->data_manager->get_lists();

		return isset( $lists->js_move_after_combine ) ? $lists->js_move_after_combine : [];
	}

	/**
	 * Get the inline JS excluded from combine JS
	 *
	 * @return array
	 */
	public function get_combine_js_excluded_inline(): array {
		$lists = $this->providers['defaultlists']->data_manager->get_lists();

		return isset( $lists->js_excluded_inline ) ? $lists->js_excluded_inline : [];
	}

	/**
	 * Get the preload exclusions
	 *
	 * @return array
	 */
	public function get_preload_exclusions(): array {
		$lists = $this->providers['defaultlists']->data_manager->get_lists();

		return isset( $lists->preload_exclusions ) ? $lists->preload_exclusions : [];
	}

	/**
	 * Get Delay JS dynamic list.
	 *
	 * @return object
	 */
	public function get_delayjs_list() {
		return $this->providers['delayjslists']->data_manager->get_lists();
	}

	/**
	 * Get the JS minify excluded files
	 *
	 * @return array
	 */
	public function get_js_exclude_files(): array {
		$lists = $this->providers['defaultlists']->data_manager->get_lists();

		return isset( $lists->exclude_js_files ) ? $lists->exclude_js_files : [];
	}

	/**
	 * Get the incompatible plugins list
	 *
	 * @return array
	 */
	public function get_incompatible_plugins() {
		$lists = $this->providers['incompatible_plugins']->data_manager->get_plugins_list();

		return isset( $lists ) ? $lists : [];
	}

	/**
	 * Get the staging list
	 *
	 * @return array
	 */
	public function get_stagings(): array {
		$lists = $this->providers['defaultlists']->data_manager->get_lists();

		return isset( $lists->staging_domains ) ? $lists->staging_domains : [];
	}

	/**
	 * Get the JS minify excluded templates
	 *
	 * @return array
	 */
	public function get_exclude_js_templates(): array {
		$lists = $this->providers['defaultlists']->data_manager->get_lists();

		return $lists->exclude_js_template ?? [];
	}

	/**
	 * Get the lazy rendered exclusions.
	 *
	 * @return array
	 */
	public function get_lrc_exclusions(): array {
		$lists = $this->providers['defaultlists']->data_manager->get_lists();

		return $lists->lazy_rendering_exclusions ?? [];
	}

	/**
	 * Updates the lists from JSON files
	 *
	 * @return void
	 */
	public function update_lists_from_files() {
		foreach ( $this->providers as $provider ) {
			$provider->data_manager->remove_lists_cache();
			$provider->data_manager->get_lists();
		}
	}

	/**
	 * Get the host fonts excluded templates
	 *
	 * @return array
	 */
	public function get_exclude_media_fonts(): array {
		$lists = $this->providers['defaultlists']->data_manager->get_lists();

		return $lists->host_fonts ?? [];
	}

	/**
	 * Get the MixPanel tracked options
	 *
	 * @return array
	 */
	public function get_mixpanel_tracked_options(): array {
		$lists = $this->providers['defaultlists']->data_manager->get_lists();

		return $lists->mixpanel_tracked_settings ?? [];
	}

	/**
	 * Get the external font exclusions
	 *
	 * @since 3.19.1
	 * @return array
	 */
	public function get_external_font_exclusions(): array {
		$lists = $this->providers['defaultlists']->data_manager->get_lists();

		return $lists->external_font_exclusions ?? [];
	}
}
