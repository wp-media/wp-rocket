<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DelayJS\Admin;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Theme;

class SiteList {
	/**
	 * Delay JS data manager.
	 *
	 * @var DynamicLists
	 */
	protected $dynamic_lists;

	/**
	 * Options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Options API instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * SiteList Constructor.
	 *
	 * @param DynamicLists $dynamic_lists Dynamic Lists instance.
	 * @param Options_Data $options Options instance.
	 * @param Options      $options_api Options API instance.
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
		return ! empty( $scripts[ $item_id ] ) ? (array) $scripts[ $item_id ] : [];
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
	 * Get all plugins from the list.
	 *
	 * @return array
	 */
	private function get_plugins_from_list() {
		$list = $this->dynamic_lists->get_delayjs_list();
		return ! empty( $list->plugins ) ? (array) $list->plugins : [];
	}

	/**
	 * Get all themes from the list.
	 *
	 * @return array
	 */
	private function get_themes_from_list() {
		$list = $this->dynamic_lists->get_delayjs_list();
		return ! empty( $list->themes ) ? (array) $list->themes : [];
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
		return $this->get_theme_name( wp_get_theme() );
	}

	/**
	 * Get active plugins (list of IDs).
	 *
	 * @return array
	 */
	public function get_active_plugins() {
		$plugins = (array) get_option( 'active_plugins', [] );

		if ( ! is_multisite() ) {
			return $plugins;
		}

		return array_merge(
			$plugins,
			array_keys( (array) get_site_option( 'active_sitewide_plugins', [] ) )
		);
	}

	/**
	 * Prepare the list of scripts, plugins and theme for the view.
	 *
	 * @return array|array[]
	 */
	public function prepare_delayjs_ui_list() {
		$full_list = [
			'scripts' => [
				'title'          => __( 'Analytics & Ads', 'rocket' ),
				'items'          => [],
				'dashicon-class' => 'analytics',
			],
			'plugins' => [
				'title'          => __( 'Plugins', 'rocket' ),
				'items'          => [],
				'dashicon-class' => 'admin-plugins',
			],
			'themes'  => [
				'title'          => __( 'Themes', 'rocket' ),
				'items'          => [],
				'dashicon-class' => 'admin-appearance',
			],
		];

		// Scripts.
		$scripts = $this->get_scripts_from_list();
		foreach ( $scripts as $script_key => $script ) {
			$full_list['scripts']['items'][] = [
				'id'    => $script_key,
				'title' => $script->title,
				'icon'  => $this->get_icon( $script ),
			];
		}

		$active_plugins = $this->get_active_plugins();
		foreach ( $this->get_plugins_from_list() as $plugin_key => $plugin ) {
			if ( ! in_array( $plugin->condition, $active_plugins, true ) ) {
				continue;
			}

			$full_list['plugins']['items'][] = [
				'id'    => $plugin_key,
				'title' => $plugin->title,
				'icon'  => $this->get_icon( $plugin ),
			];
		}

		$active_theme = $this->get_active_theme();
		foreach ( $this->get_themes_from_list() as $theme_key => $theme ) {
			if ( $theme->condition !== $active_theme ) {
				continue;
			}

			$full_list['themes']['items'][] = [
				'id'    => $theme_key,
				'title' => $theme->title,
				'icon'  => $this->get_icon( $theme ),
			];
		}

		return $full_list;
	}

	/**
	 * Fetch the icon.
	 *
	 * @param object $item item from the list.
	 * @return string
	 */
	private function get_icon( $item ) {
		if ( empty( $item->icon_url ) ) {
			return '';
		}

		return $item->icon_url;
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
	public function sanitize_options( $input ): array {
		if ( empty( $input['delay_js_exclusions_selected'] ) ) {
			$input['delay_js_exclusions_selected']            = [];
			$input['delay_js_exclusions_selected_exclusions'] = [];

			return $input;
		}

		$input['delay_js_exclusions_selected_exclusions'] = $this->get_delayjs_items_exclusions( $input['delay_js_exclusions_selected'] );

		return $input;
	}

	/**
	 * Refresh exclusions option based on selected items option.
	 *
	 * @return void
	 */
	public function refresh_exclusions_option() {
		$slug    = rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );
		$options = get_option( $slug, [] );
		if ( empty( $options ) ) {
			return;
		}
		$options['delay_js_exclusions_selected_exclusions'] = $this->get_delayjs_items_exclusions( $options['delay_js_exclusions_selected'] ?? [] );
		update_option( $slug, $options );
	}

	/**
	 * Get plugin item ids from the list using plugin base.
	 *
	 * @param string $plugin_base Plugin basename.
	 *
	 * @return string[]
	 */
	private function get_plugin_item_ids( string $plugin_base ) {
		$item_ids = [];
		foreach ( $this->get_plugins_from_list() as $plugin_key => $plugin ) {
			if ( $plugin_base !== $plugin->condition ) {
				continue;
			}
			$item_ids[ $plugin_key ] = $plugin->is_default;
		}
		return $item_ids;
	}

	/**
	 * Add plugin exclusions only if plugin is default is checked in backend.
	 *
	 * @param string $plugin_base Plugin basename.
	 *
	 * @return void
	 */
	public function add_default_plugin_exclusions( string $plugin_base ) {
		$plugin_item_ids = $this->get_plugin_item_ids( $plugin_base );
		if ( empty( $plugin_item_ids ) ) {
			return;
		}

		$selected_items = $this->options->get( 'delay_js_exclusions_selected', [] );
		if ( empty( $selected_items ) ) {
			return;
		}

		$current_selected_items = $selected_items;

		foreach ( $plugin_item_ids as $plugin_item_id => $plugin_is_default ) {
			if ( ! $plugin_is_default ) {
				continue;
			}
			$selected_items[] = $plugin_item_id;
		}

		if ( $current_selected_items === $selected_items ) {
			return;
		}

		$this->options->set( 'delay_js_exclusions_selected', $selected_items );
		$this->options->set(
			'delay_js_exclusions_selected_exclusions',
			$this->get_delayjs_items_exclusions( $selected_items )
		);

		$this->options_api->set( 'settings', $this->options->get_options() );
	}

	/**
	 * Remove plugin selections from settings.
	 *
	 * @param string $plugin_base Plugin basename.
	 *
	 * @return void
	 */
	public function remove_plugin_selection( $plugin_base ) {
		$plugin_item_ids = $this->get_plugin_item_ids( $plugin_base );
		if ( empty( $plugin_item_ids ) ) {
			return;
		}

		$selected_items = $this->options->get( 'delay_js_exclusions_selected', [] );
		if ( empty( $selected_items ) ) {
			return;
		}

		$current_selected_items = $selected_items;

		foreach ( $plugin_item_ids as $plugin_item_id => $plugin_is_default ) {
			$selected_item_key = array_search( $plugin_item_id, $selected_items, true );
			if ( false === $selected_item_key ) {
				continue;
			}
			unset( $selected_items[ $selected_item_key ] );
		}

		if ( $current_selected_items === $selected_items ) {
			return;
		}

		$this->options->set( 'delay_js_exclusions_selected', $selected_items );
		$this->options->set(
			'delay_js_exclusions_selected_exclusions',
			$this->get_delayjs_items_exclusions( $selected_items )
		);

		$this->options_api->set( 'settings', $this->options->get_options() );
	}

	/**
	 * Get theme name from theme object.
	 *
	 * @param WP_Theme $theme Theme to get its name.
	 *
	 * @return string
	 */
	private function get_theme_name( WP_Theme $theme ) {
		$parent = $theme->get_template();
		if ( ! empty( $parent ) ) {
			return $parent;
		}

		return $theme->get( 'Name' );
	}

	/**
	 * Get Theme item ids from the list using theme name.
	 *
	 * @param string $theme_name Theme name.
	 *
	 * @return string[]
	 */
	private function get_theme_item_ids( $theme_name ) {
		$item_ids = [];
		foreach ( $this->get_themes_from_list() as $theme_key => $theme ) {
			if ( $theme_name !== $theme->condition ) {
				continue;
			}

			$item_ids[] = $theme_key;
		}
		return $item_ids;
	}

	/**
	 * Replace the old theme with the new theme exclusions.
	 *
	 * @param WP_Theme $new_theme WP_Theme instance of the new theme.
	 * @param WP_Theme $old_theme WP_Theme instance of the old theme.
	 *
	 * @return void
	 */
	public function replace_theme_selection( $new_theme, $old_theme ) {
		$new_theme_ids = $this->get_theme_item_ids( $this->get_theme_name( $new_theme ) );
		$old_theme_ids = $this->get_theme_item_ids( $this->get_theme_name( $old_theme ) );

		if ( empty( $new_theme_ids ) && empty( $old_theme_ids ) ) {
			return;
		}

		$selected_items = $this->options->get( 'delay_js_exclusions_selected', [] );
		if ( empty( $selected_items ) ) {
			return;
		}

		$current_selected_items = $selected_items;

		if ( ! empty( $old_theme_ids ) ) {
			foreach ( $old_theme_ids as $old_theme_id ) {
				$selected_item_key = array_search( $old_theme_id, $selected_items, true );
				if ( false === $selected_item_key ) {
					continue;
				}
				unset( $selected_items[ $selected_item_key ] );
			}
		}

		if ( ! empty( $new_theme_ids ) ) {
			$themes = $this->get_themes_from_list();
			foreach ( $new_theme_ids as $new_theme_id ) {
				if ( ! $themes[ $new_theme_id ]->is_default ) {
					continue;
				}
				$selected_items[] = $new_theme_id;
			}
		}

		if ( $current_selected_items === $selected_items ) {
			return;
		}

		$this->options->set( 'delay_js_exclusions_selected', $selected_items );
		$this->options->set(
			'delay_js_exclusions_selected_exclusions',
			$this->get_delayjs_items_exclusions( $selected_items )
		);

		$this->options_api->set( 'settings', $this->options->get_options() );
	}

	/**
	 * Get default items from the list with their exclusions.
	 *
	 * @return array
	 */
	public function get_default_exclusions() {
		$items = [];

		$scripts = $this->get_scripts_from_list();
		foreach ( $scripts as $script_key => $script ) {
			if ( ! $script->is_default ) {
				continue;
			}

			$items[ $script_key ] = $script->exclusions;
		}

		$active_plugins = $this->get_active_plugins();
		foreach ( $this->get_plugins_from_list() as $plugin_key => $plugin ) {
			if ( ! in_array( $plugin->condition, $active_plugins, true ) || ! $plugin->is_default ) {
				continue;
			}

			$items[ $plugin_key ] = $plugin->exclusions;
		}

		$active_theme = $this->get_active_theme();
		foreach ( $this->get_themes_from_list() as $theme_key => $theme ) {
			if ( $theme->condition !== $active_theme || ! $theme->is_default ) {
				continue;
			}

			$items[ $theme_key ] = $theme->exclusions;
		}

		/**
		 * Filters the delay JS default exclusions list.
		 * Key is the plugin/theme/script unique ID and value is array of exclusions
		 *
		 * @since 3.13
		 *
		 * @param array $items Array of default excluded items.
		 */
		return apply_filters( 'rocket_delay_js_default_exclusions', $items );
	}
}
