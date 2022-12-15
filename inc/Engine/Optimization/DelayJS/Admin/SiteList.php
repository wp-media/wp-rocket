<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DelayJS\Admin;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DynamicLists\DelayJSLists\DataManager;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;

class SiteList {
	/**
	 * Delay JS data manager.
	 *
	 * @var DataManager
	 */
	protected $dynamic_lists;

	/**
	 * Options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Options instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * SiteList Constructor.
	 *
	 * @param DynamicLists $dynamic_lists Dynamic Lists instance.
	 * @param Options_Data $options Options instance.
	 * @param Options      $options_api Options instance.
	 */
	public function __construct( DynamicLists $dynamic_lists, Options_Data $options, Options $options_api ) {
		$this->dynamic_lists = $dynamic_lists;
		$this->options       = $options;
		$this->options_api   = $options_api;
	}

	/**
	 * Check if plugin is in the list and return it if found.
	 *
	 * @param string $item_id Plugin ID like wp-rocket/wp-rocket.php.
	 *
	 * @return array
	 */
	private function get_plugin_in_list( string $item_id ) {
		$list = $this->dynamic_lists->get_delayjs_list();
		return ! empty( $list->plugins->$item_id ) ? (array) $list->plugins->$item_id : [];
	}

	/**
	 * Check if theme is in the list and return it if found.
	 *
	 * @param string $item_id Theme ID (directory name) like twentytwenty.
	 *
	 * @return array
	 */
	private function get_theme_in_list( string $item_id ) {
		$list = $this->dynamic_lists->get_delayjs_list();
		return ! empty( $list->themes->$item_id ) ? (array) $list->themes->$item_id : [];
	}

	/**
	 * Check if script is in the list and return it if found.
	 *
	 * @param string $item_id Script ID.
	 *
	 * @return array
	 */
	private function get_script_in_list( string $item_id ) {
		$scripts = $this->get_scripts_from_list();
		return ! empty( $scripts->$item_id ) ? (array) $scripts->$item_id : [];
	}

	/**
	 * Get all scripts from the list.
	 *
	 * @return array
	 */
	private function get_scripts_from_list() {
		$list = $this->dynamic_lists->get_delayjs_list();
		return ! empty( $list->scripts ) ? (array) $list->scripts : [];
	}

	/**
	 * Get list of exclusions from the API list.
	 *
	 * @param string $item_id Item ID to get exclusions for (plugin slug, theme slug, script ID).
	 *
	 * @return array
	 */
	public function get_delayjs_exclusions_by_id( string $item_id ) {
		$item = $this->get_script_in_list( $item_id );
		if ( $item ) {
			return $item['exclusions'];
		}

		$item = $this->get_plugin_in_list( $item_id );
		if ( $item ) {
			return $item['exclusions'];
		}

		$item = $this->get_theme_in_list( $item_id );
		if ( $item ) {
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
		if ( empty( $items ) ) {
			return [];
		}

		$exclusions = [];

		foreach ( $items as $item ) {
			$exclusions = array_merge( $exclusions, $this->get_delayjs_exclusions_by_id( $item ) );
		}

		return $exclusions;
	}

	/**
	 * Get active theme ID.
	 *
	 * @return string
	 */
	public function get_active_theme() {
		$theme  = wp_get_theme();
		$parent = $theme->get_template();
		if ( ! empty( $parent ) ) {
			return strtolower( $parent );
		}

		return strtolower( $theme->get( 'Name' ) );
	}

	/**
	 * Get active plugins (list of IDs).
	 *
	 * @return array
	 */
	public function get_active_plugins() {
		return (array) get_option( 'active_plugins', [] );
	}

	/**
	 * Prepare the list of scripts, plugins and theme for the view.
	 *
	 * @return array|array[]
	 */
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
			'themes'  => [
				'title' => __( 'Themes', 'rocket' ),
				'items' => [],
			],
		];

		// Scripts.
		$scripts = $this->get_scripts_from_list();
		foreach ( $scripts as $script ) {
			$script                          = (array) $script;
			$full_list['scripts']['items'][] = [
				'id'    => $script['condition'],
				'title' => $script['title'],
				'icon'  => $this->get_icon( $script ),
			];
		}

		foreach ( $this->get_active_plugins() as $plugin ) {
			$plugin_in_list = $this->get_plugin_in_list( $plugin );
			if ( empty( $plugin_in_list ) ) {
				continue;
			}

			$full_list['plugins']['items'][] = [
				'id'    => $plugin,
				'title' => $plugin_in_list['title'],
				'icon'  => $this->get_icon( $plugin_in_list ),
			];
		}

		$theme_in_list = $this->get_theme_in_list( $this->get_active_theme() );
		if ( ! empty( $theme_in_list ) ) {
			$full_list['themes']['items'][] = [
				'id'    => $theme_in_list['condition'],
				'title' => $theme_in_list['title'],
				'icon'  => $this->get_icon( $theme_in_list ),
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
			return rocket_get_constant( 'WP_ROCKET_ASSETS_IMG_URL' ) . 'default-icon.png';
		}

		return $item['icon_url'];
	}

	/**
	 * Sanitizes delay JS options when saving the settings
	 *
	 * @since 3.13
	 *
	 * @param array $input Array of values submitted from the form.
	 *
	 * @return array
	 */
	public function sanitize_options( $input ) : array {
		if ( empty( $input['delay_js_exclusions_selected'] ) ) {
			$input['delay_js_exclusions_selected']            = [];
			$input['delay_js_exclusions_selected_exclusions'] = [];
		} else {
			$input['delay_js_exclusions_selected_exclusions'] = $this->get_delayjs_items_exclusions( $input['delay_js_exclusions_selected'] );
		}

		return $input;
	}

	/**
	 * Refresh exclusions option based on selected items option.
	 *
	 * @return void
	 */
	public function refresh_exclusions_option() {
		$selected_items = $this->options->get( 'delay_js_exclusions_selected' );

		$this->options->set(
			'delay_js_exclusions_selected_exclusions',
			$this->get_delayjs_items_exclusions( $selected_items )
		);

		$this->options_api->set( 'settings', $this->options->get_options() );
	}
}
