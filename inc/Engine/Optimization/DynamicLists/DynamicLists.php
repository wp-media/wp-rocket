<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DynamicLists;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\License\API\User;
use WP_REST_Response;
use WP_Error;
use stdClass;

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

		$code = 200;

		foreach ( $this->providers as $provider ) {
			$result = $provider->api_client->get_exclusions_list( $provider->data_manager->get_lists_hash() );

			if ( empty( $result['code'] ) || empty( $result['body'] ) ) {
				$code = 0;
				continue;
			}

			if ( 200 !== $result['code'] ) {
				$code = $result['code'];
				continue;
			}

			if ( ! $provider->data_manager->save_dynamic_lists( $result['body'] ) ) {
				$code = 'NOT_SAVED';
			}
		}

		switch ( $code ) {
			case 0:
				return [
					'success' => false,
					'data'    => '',
					'message' => __( 'Could not get updated lists from server.', 'rocket' ),
				];
			case 206:
				return [
					'success' => true,
					'data'    => '',
					'message' => __( 'Lists are up to date.', 'rocket' ),
				];
			case 'NOT_SAVED':
				return [
					'success' => false,
					'data'    => '',
					'message' => __( 'Could not update lists.', 'rocket' ),
				];
			default:
				/**
				 * Fires after saving all dynamic lists files.
				 *
				 * @since 3.12.1
				 */
				do_action( 'rocket_after_save_dynamic_lists' );

				return [
					'success' => true,
					'data'    => '',
					'message' => __( 'Lists are successfully updated.', 'rocket' ),
				];
		}
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

	private function get_plugin_in_list( string $item_id ) {
		$list = $this->providers['delayjslists']->data_manager->get_lists();
		return ! empty( $list->plugins->$item_id ) ? (array) $list->plugins->$item_id : [];
	}

	private function get_theme_in_list( string $item_id ) {
		$list = $this->providers['delayjslists']->data_manager->get_lists();
		return ! empty( $list->themes->$item_id ) ? (array) $list->themes->$item_id : [];
	}

	private function get_script_in_list( string $item_id ) {
		$list = $this->providers['delayjslists']->data_manager->get_lists();
		return ! empty( $list->scripts->$item_id ) ? (array) $list->scripts->$item_id : [];
	}

	private function get_scripts_from_list() {
		$list = $this->providers['delayjslists']->data_manager->get_lists();
		return $list->scripts ?? new StdClass;
	}

	/**
	 * Get list of exclusions from the API list.
	 *
	 * @param string $item_id Item ID to get exclusions for (plugin slug, theme slug, script ID).
	 *
	 * @return array
	 */
	public function get_delayjs_exclusions_by_id( string $item_id ) {
		$list = $this->providers['delayjslists']->data_manager->get_lists();

		if ( $item = $this->get_script_in_list( $item_id ) ) {
			return $item['exclusions'];
		}

		if ( $item = $this->get_plugin_in_list( $item_id ) ) {
			return $item['exclusions'];
		}

		if ( $item = $this->get_theme_in_list( $item_id ) ) {
			return $item['exclusions'];
		}

		return [];
	}

	/**
	 * Get all exclusions (merged together) for a list of item IDs.
	 *
	 * @param array $items List of items.
	 *
	 * @return array
	 */
	public function get_delayjs_items_exclusions( array $items ) {
		$exclusions = [];

		foreach ( $items as $item ) {
			array_merge( $exclusions, $this->get_delayjs_exclusions_by_id( $item ) );
		}

		return $exclusions;
	}

	public function get_active_theme() {
		$theme = wp_get_theme();
		$parent = $theme->get_template();
		if ( ! empty( $parent ) ) {
			return strtolower( $parent );
		}

		return strtolower( $theme->get( 'Name' ) );
	}

	public function get_active_plugins() {
		return (array) get_option( 'active_plugins', array() );
	}

	public function prepare_delayjs_ui_list() {
		$full_list = [
			'scripts' => [
				'title' => __( 'Analytics & Ads', 'rocket' ),
				'items' => [],
			],
			'plugins' => [
				'title' => __( 'Plugins', 'rocket' ),
				'items' => [],
			],
			'themes' => [
				'title' => __( 'Themes', 'rocket' ),
				'items' => [],
			],
		];

		// Scripts.
		$scripts = $this->get_scripts_from_list();
		if ( ! empty( $scripts ) ) {
			foreach ( $scripts as $script ) {
				$full_list['scripts']['items'][] = [
					'id'    => $script['condition'],
					'title' => $script['title'],
					'icon'  => $this->get_icon( $script ),
				];
			}
		}

		foreach ( $this->get_active_plugins() as $plugin ) {
			$plugin_in_list = $this->get_plugin_in_list( $plugin );
			if ( empty( $plugin_in_list ) ) {
				continue;
			}

			$full_list['plugins']['items'][] = [
				'id' => $plugin,
				'title' => $plugin_in_list['title'],
				'icon' => $this->get_icon( $plugin_in_list ),
			];
		}

		$theme_in_list = $this->get_theme_in_list( $this->get_active_theme() );
		if ( ! empty( $theme_in_list ) ) {
			$full_list['themes']['items'][] = [
				'id' => $theme_in_list['condition'],
				'title' => $theme_in_list['title'],
				'icon' => $this->get_icon( $theme_in_list ),
			];
		}

		return $full_list;
	}
	/**
	 * Fetch the icon.
	 *
	 * @param array $item item from the list.
	 * @return string
	 */
	private function get_icon( $item ) {
		if ( empty( $item ) || empty( $item['icon_url'] ) ) {
			return esc_url( rocket_get_constant( 'WP_ROCKET_ASSETS_IMG_URL' ) . 'default-icon.png' );
		}

		return esc_url( $item['icon_url'] );
	}
}
